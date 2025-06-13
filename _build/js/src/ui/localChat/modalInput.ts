import { applyStyles, createElement } from '../utils';
import { emitter } from './emitter';
import { addErrorMessage } from './messageHandlers';
import {
  clearChat,
  handleImageUpload,
  sendMessage,
  stopGeneration,
  switchType,
} from './modalActions';
import { buildModalInputAttachments } from './modalInputAttachments';
import { buildModalInputContexts } from './modalInputContext';
import { button } from '../dom/button';
import { icon } from '../dom/icon';
import { image, arrowUp, square, text, trash, bot, library } from '../icons';
import { buildScrollToBottom } from './scrollBottom';
import { globalState } from '../../globalState';
import { lng } from '../../lng';
import { buildNestedSelect } from '../dom/nestedSelect';
import { buildSelect, Select } from '../dom/select';

import type { LocalChatConfig, ModalType } from './types';
import type { Button } from '../dom/button';

export type ModeButton = Button & {
  mode: ModalType;
  activate: () => void;
};

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

    textarea.dispatchEvent(new Event('input', { bubbles: true, cancelable: true }));
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

  const sendBtn = button(icon(20, arrowUp), () => sendMessage(), '', {
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

  const inputAddons = createElement('div', 'inputAddons', [
    buildModalInputAttachments(),
    buildModalInputContexts(),
  ]);

  inputSection.append(inputAddons, inputWrapper);

  const modeButtons: ModeButton[] = [];
  if (config.availableTypes?.includes('text')) {
    const textModeBtn = button(
      [icon(24, text), createElement('span', 'tooltip', lng('modai.ui.text_mode'))],
      () => {
        if (config.type === 'text') {
          return;
        }

        switchType('text');
        modeButtons.forEach((btn) => {
          applyStyles(btn, '');
        });
        applyStyles(textModeBtn, 'active');
      },
      '',
      {
        ariaLabel: lng('modai.ui.text_mode'),
      },
    ) as ModeButton;
    textModeBtn.activate = () => {
      modeButtons.forEach((btn) => {
        applyStyles(btn, '');
      });
      applyStyles(textModeBtn, 'active');
    };
    textModeBtn.mode = 'text';

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

        switchType('image');
        modeButtons.forEach((btn) => {
          applyStyles(btn, '');
        });
        applyStyles(imageModeBtn, 'active');
      },
      '',
      {
        ariaLabel: lng('modai.ui.image_mode'),
      },
    ) as ModeButton;
    imageModeBtn.activate = () => {
      modeButtons.forEach((btn) => {
        applyStyles(btn, '');
      });
      applyStyles(imageModeBtn, 'active');
    };
    imageModeBtn.mode = 'image';

    if (config.type === 'image') {
      applyStyles(imageModeBtn, 'active');
    }
    modeButtons.push(imageModeBtn);
  }

  let additionalOptions: (Button | Select)[] = [];

  const options = createElement('div', 'options', [], {
    ariaLabel: lng('modai.ui.options_toolbar'),
    role: 'toolbar',
  });

  const optionsLeft = createElement('div', 'optionsLeft');
  const optionsRight = createElement('div', 'optionsRight');

  options.append(optionsLeft, optionsRight);

  let clearChatBtn: null | Button;
  if (!config.persist) {
    clearChatBtn = button(
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
    optionsRight.append(clearChatBtn);

    // globalState.modal.actionButtons.push(clearChatBtn);
  }

  let controlButtons: (Button | Select)[] = [];
  const loadChatControls = () => {
    controlButtons = [];
    optionsLeft.innerHTML = '';

    additionalOptions = [];
    if (config.type === 'text' && Object.keys(globalState.config.availableAgents).length > 0) {
      const agentSelectComponent = buildSelect(
        globalState.config.availableAgents,
        globalState.selectedAgent[`${config.key}/${config.type}`]?.id,
        (selectedAgent) => {
          globalState.selectedAgent[`${config.key}/${config.type}`] = selectedAgent ?? undefined;
        },
        {
          idProperty: 'id',
          displayProperty: 'name',
          noSelectionText: lng('modai.ui.agents'),
          selectText: lng('modai.ui.select_agent'),
          nullOptionDisplayText: lng('modai.ui.no_agent'),
          icon: bot,
          tooltip: lng('modai.ui.select_agent'),
        },
      );

      additionalOptions.push(agentSelectComponent);
    }

    if (globalState.config.chatAdditionalControls[config.type]) {
      globalState.config.chatAdditionalControls[config.type].forEach((item) => {
        additionalOptions.push(
          buildSelect(
            Object.entries(item.values).reduce(
              (acc, [value, name]) => {
                acc[value] = { name, value };
                return acc;
              },
              {} as Record<string, { name: string; value: string }>,
            ),
            globalState.additionalControls[`${config.key}/${config.type}`]?.[item.name]?.value,
            (selected) => {
              const controlsKey = `${config.key}/${config.type}`;

              if (!globalState.additionalControls[controlsKey]) {
                globalState.additionalControls[controlsKey] = {};
              }

              if (selected) {
                globalState.additionalControls[controlsKey][item.name] = selected;
              } else {
                delete globalState.additionalControls[controlsKey][item.name];
              }
            },
            {
              idProperty: 'value',
              displayProperty: 'name',
              noSelectionText: item.label,
              selectText: item.label,
              nullOptionDisplayText: `Default ${item.label}`,
              icon: item.icon,
              tooltip: `Select ${item.label}`,
            },
          ),
        );
      });
    }

    const promptsForType = globalState.config.promptLibrary[config.type];
    if (promptsForType && promptsForType.length > 0) {
      const promptLibrary = buildNestedSelect(
        promptsForType,
        undefined,
        (item) => {
          if ('value' in item) {
            textarea.setValue(item.value);
          }
        },
        {
          icon: library,
          tooltip: lng('modai.ui.prompt_library'),
          showSelectedValue: false,
          highlightSelectedValue: false,
        },
      );

      controlButtons.push(promptLibrary);
    }

    controlButtons.push(...additionalOptions);

    optionsLeft.append(...modeButtons, ...controlButtons);
    globalState.modal.controlButtons = controlButtons;
  };

  loadChatControls();

  const scrollWrapper = buildScrollToBottom();
  container.append(scrollWrapper);

  container.append(inputSection, options);

  textarea.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      if (e.shiftKey) {
        return;
      }

      e.preventDefault();
      void sendMessage();
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
      textarea.focus();
      return;
    }

    if (remoteImageUrl) {
      const url = new URL(window.location.href);
      const isRemote = !remoteImageUrl.startsWith(url.origin);

      if (isRemote) {
        await handleImageUpload(remoteImageUrl, true);
        textarea.focus();
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
      textarea.focus();
      return;
    }

    addErrorMessage(lng('modai.error.only_image_files_are_allowed'));
    textarea.focus();
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

  emitter.on('loading', ({ eventData }) => {
    sendBtn.disable();
    textarea.disabled = eventData.isLoading;

    if (eventData.isLoading) {
      loading.style.display = 'flex';

      if (!eventData.isPreloading) {
        stopBtn.enable();
      }
    } else {
      loading.style.display = 'none';

      if (!eventData.isPreloading) {
        stopBtn.disable();
      }
    }

    [...modeButtons, ...controlButtons].forEach((btn) =>
      eventData.isLoading ? btn.disable() : btn.enable(),
    );

    if (clearChatBtn) {
      if (eventData.isLoading || !eventData.hasMessages) {
        clearChatBtn.disable();
      } else {
        clearChatBtn.enable();
      }
    }
  });

  globalState.modal.messageInput = textarea;
  globalState.modal.modeButtons = modeButtons;
  globalState.modal.reloadChatControls = loadChatControls;

  globalState.modal.enableSending = () => {
    textarea.disabled = false;
    textarea.placeholder = lng('modai.ui.prompt_placeholder');
    globalState.modal.controlButtons.forEach((btn) => btn.enable());
  };
  globalState.modal.disableSending = () => {
    textarea.disabled = true;
    textarea.placeholder = lng('modai.ui.read_only_chat');
    globalState.modal.controlButtons.forEach((btn) => btn.disable());
  };

  return container;
};
