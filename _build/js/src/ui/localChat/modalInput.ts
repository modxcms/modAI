import { applyStyles, createElement } from '../utils';
import { addErrorMessage } from './messageHandlers';
import {
  clearChat,
  handleImageUpload,
  sendMessage,
  stopGeneration,
  switchType,
  tryAgain,
} from './modalActions';
import { buildModalInputAttachments } from './modalInputAttachments';
import { buildModalInputContexts } from './modalInputContext';
import { button } from '../dom/button';
import { icon } from '../dom/icon';
import { image, refresh, arrowUp, square, text, trash } from '../icons';
import { buildScrollToBottom } from './scrollBottom';
import { globalState } from '../../globalState';
import { lng } from '../../lng';

import type { LocalChatConfig } from './types';
import type { Button } from '../dom/button';

export type UserInput = HTMLTextAreaElement & {
  setValue: (value: string) => void;
};

export const buildModalInput = (config: LocalChatConfig) => {
  const container = createElement('div', 'inputContainer');

  const inputSection = createElement('div', 'inputSection');
  const inputWrapper = createElement('div', 'inputWrapper');

  const textarea = createElement('textarea', '', '', {
    placeholder: lng('modai.ui.prompt_placeholder'),
    rows: 1,
    ariaLabel: lng('modai.ui.prompt_label'),
  }) as UserInput;

  textarea.setValue = (value: string) => {
    textarea.value = value;
    textarea.focus();

    if (value.trim() !== '') {
      sendBtn.disabled = false;
      applyStyles(sendBtn, 'active');
    } else {
      sendBtn.disabled = true;
      applyStyles(sendBtn, '');
    }
  };

  const loading = createElement(
    'div',
    'loadingDots',
    [
      createElement('div', 'loadingDot'),
      createElement('div', 'loadingDot'),
      createElement('div', 'loadingDot'),
    ],
    { ariaLabel: lng('modai.ui.loading_response') },
  );

  const sendBtn = button(icon(20, arrowUp), () => sendMessage(config), '', {
    ariaLabel: lng('modai.ui.send_message'),
  });
  sendBtn.disable();

  sendBtn.enable = () => {
    sendBtn.disabled = false;
    applyStyles(sendBtn, 'active');
  };

  sendBtn.disable = () => {
    sendBtn.disabled = true;
    applyStyles(sendBtn, '');
  };

  const stopBtn = button(icon(20, square), () => stopGeneration(), '', {
    ariaLabel: lng('modai.ui.stop_generating_response'),
  });
  stopBtn.disable();

  stopBtn.enable = () => {
    stopBtn.disabled = false;
    applyStyles(stopBtn, 'active sending');
  };

  stopBtn.disable = () => {
    stopBtn.disabled = true;
    applyStyles(stopBtn, '');
  };

  inputWrapper.append(textarea, loading, sendBtn, stopBtn);

  inputSection.append(buildModalInputAttachments(), inputWrapper);
  inputSection.append(buildModalInputContexts(), inputWrapper);

  const modeButtons: Button[] = [];

  if (config.availableTypes?.includes('text')) {
    const textModeBtn = button(
      [icon(24, text), createElement('span', 'tooltip', lng('modai.ui.text_mode'))],
      () => {
        if (config.type === 'text') {
          return;
        }

        switchType('text', config);
        modeButtons.forEach((btn) => {
          applyStyles(btn, '');
        });
        applyStyles(textModeBtn, 'active');
      },
      '',
      {
        ariaLabel: lng('modai.ui.text_mode'),
      },
    );

    if (config.type === 'text') {
      applyStyles(textModeBtn, 'active');
    }
    modeButtons.push(textModeBtn);
  }

  if (config.availableTypes?.includes('image')) {
    const imageModeBtn = button(
      [icon(24, image), createElement('span', 'tooltip', lng('modai.ui.image_mode'))],
      () => {
        if (config.type === 'image') {
          return;
        }

        switchType('image', config);
        modeButtons.forEach((btn) => {
          applyStyles(btn, '');
        });
        applyStyles(imageModeBtn, 'active');
      },
      '',
      {
        ariaLabel: lng('modai.ui.image_mode'),
      },
    );
    if (config.type === 'image') {
      applyStyles(imageModeBtn, 'active');
    }

    modeButtons.push(imageModeBtn);
  }

  const tryAgainBtn = button(
    [icon(24, refresh), createElement('span', 'tooltip', lng('modai.ui.retry_last_message'))],
    () => {
      tryAgain(config);
    },
    '',
    {
      ariaLabel: lng('modai.ui.retry_last_message'),
    },
  );
  tryAgainBtn.disable();

  const clearChatBtn = button(
    [icon(24, trash), createElement('span', 'tooltip', lng('modai.ui.clear_chat'))],
    () => {
      clearChat();
    },
    '',
    {
      ariaLabel: lng('modai.ui.clear_chat'),
    },
  );
  clearChatBtn.disable();

  const agent = createElement('select', undefined, [
    createElement('option', undefined, 'No Agent', { value: '' }),
    ...Object.values(globalState.config.availableAgents).map((agent) =>
      createElement('option', undefined, agent, { value: agent }),
    ),
  ]);

  const options = createElement(
    'div',
    'options',
    [...modeButtons, tryAgainBtn, clearChatBtn, agent],
    {
      ariaLabel: lng('modai.ui.clear_options'),
      role: 'toolbar',
    },
  );

  const scrollWrapper = buildScrollToBottom();
  container.append(scrollWrapper);

  container.append(inputSection, options);

  textarea.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      if (e.shiftKey) {
        return;
      }

      e.preventDefault();
      void sendMessage(config);
    }
  });

  textarea.addEventListener('input', function () {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';

    if (this.value.trim() !== '') {
      sendBtn.disabled = false;
      applyStyles(sendBtn, 'active');
    } else {
      sendBtn.disabled = true;
      applyStyles(sendBtn, '');
    }
  });

  inputSection.addEventListener('dragover', (e) => {
    e.preventDefault();
    e.stopPropagation();
    applyStyles(inputSection, 'inputSection dragOver');
  });

  inputSection.addEventListener('dragleave', (e) => {
    e.preventDefault();
    e.stopPropagation();
    applyStyles(inputSection, 'inputSection');
  });

  inputSection.addEventListener('drop', async (e) => {
    e.preventDefault();
    e.stopPropagation();

    applyStyles(inputSection, 'inputSection');

    let imageFile: File | null = null;
    let remoteImageUrl: string | null = null;

    const dataTransfer = e.dataTransfer;
    if (!dataTransfer) return;

    const files = dataTransfer.files;
    if (files?.length > 0) {
      const file = files[0];
      if (file.type.startsWith('image/')) {
        imageFile = file;
      }
    }

    if (!imageFile) {
      const imgUrl = dataTransfer.getData('text/uri-list');
      if (imgUrl) {
        remoteImageUrl = imgUrl;
      }
    }

    if (imageFile) {
      await handleImageUpload(imageFile);
      return;
    }

    if (remoteImageUrl) {
      const url = new URL(window.location.href);
      const isRemote = !remoteImageUrl.startsWith(url.origin);

      if (isRemote) {
        await handleImageUpload(remoteImageUrl, true);
        return;
      }

      try {
        const response = await fetch(remoteImageUrl);
        if (response.ok) {
          const blob = await response.blob();
          if (blob.type.startsWith('image/')) {
            const file = new File([blob], 'image.png', { type: blob.type });
            await handleImageUpload(file);
          }
        }
      } catch {
        addErrorMessage(lng('modai.error.failed_to_fetch_image'));
      }
      return;
    }

    addErrorMessage(lng('modai.error.only_image_files_are_allowed'));
  });

  textarea.addEventListener('paste', async (e) => {
    const items = e.clipboardData?.items;
    if (!items) return;

    for (let i = 0; i < items.length; i++) {
      if (items[i].type.indexOf('image') !== -1) {
        const file = items[i].getAsFile();
        if (file) {
          e.preventDefault();
          await handleImageUpload(file);
          break;
        }
      }
    }
  });

  globalState.modal.loadingIndicator = loading;
  globalState.modal.messageInput = textarea;
  globalState.modal.sendBtn = sendBtn;
  globalState.modal.stopBtn = stopBtn;
  globalState.modal.modeButtons = modeButtons;
  globalState.modal.actionButtons = [tryAgainBtn, clearChatBtn];
  globalState.modal.agent = agent;

  return container;
};
