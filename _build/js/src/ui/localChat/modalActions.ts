import { drag, endDrag } from './dragHandlers';
import { addErrorMessage, renderMessage } from './messageHandlers';
import { setLoadingState, globalState } from './state';
import { chatHistory } from '../../chatHistory';
import { executor } from '../../executor';

import type { LocalChatConfig, ModalType } from './types';
import type { Prompt } from '../../executor';

export const closeModal = () => {
  if (globalState.modal.isLoading) {
    return;
  }

  document.removeEventListener('mousemove', (e) => drag(e));
  document.removeEventListener('mouseup', () => endDrag());

  if (globalState.modal) {
    globalState.modal.remove();
  }

  globalState.modalOpen = false;
};

export const sendMessage = async (
  config: LocalChatConfig,
  providedMessage?: string,
  hidePrompt?: boolean,
) => {
  const message: Prompt = providedMessage
    ? providedMessage.trim()
    : globalState.modal.messageInput.value.trim();

  if (!message || globalState.modal.isLoading) {
    return;
  }

  setLoadingState(true);

  globalState.modal.messageInput.value = '';
  globalState.modal.messageInput.style.height = 'auto';
  globalState.modal.abortController = new AbortController();

  globalState.modal.welcomeMessage.style.display = 'none';

  let messageToSend: Prompt = message;
  if (globalState.modal.attachments.attachments.length > 0 && config.type === 'text') {
    messageToSend = [
      {
        type: 'text',
        value: message as string,
      },
      ...globalState.modal.attachments.attachments.map((at) => ({
        type: at.type,
        value: at.value,
      })),
    ];
  }

  globalState.modal.attachments.removeAttachments();
  const messages = globalState.modal.history.getMessagesHistory();
  globalState.modal.history.addUserMessage(messageToSend, hidePrompt);

  try {
    if (config.type === 'text') {
      const data = await executor.mgr.prompt.freeText(
        {
          namespace: config.namespace,
          context: config.context,
          prompt: messageToSend,
          field: config.field || '',
          messages,
        },
        (data) => {
          globalState.modal.history.updateAssistantMessage(data.id, data.content);
        },
        globalState.modal.abortController,
      );

      globalState.modal.history.updateAssistantMessage(data.id, data.content);
    }

    if (config.type === 'image') {
      const data = await executor.mgr.prompt.image(
        {
          prompt: messageToSend as string,
        },
        globalState.modal.abortController,
      );

      globalState.modal.history.addAssistantMessage(data.url, data.id, 'image');
    }

    globalState.modal.abortController = undefined;
  } catch (err) {
    if (err instanceof Error) {
      if (err.name === 'AbortError') {
        return;
      }

      setLoadingState(false);
      addErrorMessage(err.message);
      return;
    }

    addErrorMessage('Unknown error');
  }

  setLoadingState(false);
  globalState.modal.messageInput.focus();
};

export const stopGeneration = () => {
  if (!globalState.modal.isLoading || !globalState.modal.abortController) {
    return;
  }

  globalState.modal.abortController.abort();
  globalState.modal.abortController = undefined;
  setLoadingState(false);
};

export const tryAgain = (config: LocalChatConfig) => {
  if (globalState.modal.history.getMessages().length === 0) {
    return;
  }

  if (config.type === 'text') {
    void sendMessage(config, 'Try again');
    return;
  }

  if (config.type === 'image') {
    const latestUserMsg = globalState.modal.history
      .getMessages()
      .reverse()
      .find((msg) => msg.role === 'user');
    if (latestUserMsg) {
      void sendMessage(config, latestUserMsg.content as string);
    }
  }
};

export const switchType = (type: ModalType, config: LocalChatConfig) => {
  config.type = type;

  globalState.modal.history = chatHistory.init(`${config.key}/${config.type}`, (msg) => {
    return renderMessage(msg, config);
  });

  while (globalState.modal.chatMessages.firstChild) {
    globalState.modal.chatMessages.removeChild(globalState.modal.chatMessages.firstChild);
  }

  const messages = globalState.modal.history.getMessages().filter((m) => !m.hidden);
  if (messages.length > 0) {
    globalState.modal.welcomeMessage.style.display = 'none';

    messages.forEach((msg) => {
      if (msg.el) {
        globalState.modal.chatMessages.appendChild(msg.el);
      }
    });

    globalState.modal.actionButtons.forEach((btn) => {
      btn.enable();
    });
  } else {
    globalState.modal.welcomeMessage.style.display = 'block';
    globalState.modal.actionButtons.forEach((btn) => {
      btn.disable();
    });
  }

  scrollToBottom();
};

export const clearChat = () => {
  globalState.modal.history.clearHistory();
  globalState.modal.chatMessages.innerHTML = '';
  globalState.modal.welcomeMessage.style.display = 'block';

  globalState.modal.actionButtons.forEach((btn) => {
    btn.disable();
  });
};

export const handleImageUpload = async (fileOrUrl: File | string, isRemoteUrl: boolean = false) => {
  if (!isRemoteUrl && fileOrUrl instanceof File && !fileOrUrl.type.startsWith('image/')) {
    addErrorMessage('Only image files are allowed');
    return;
  }

  if (isRemoteUrl) {
    globalState.modal.attachments.addImageAttachment(fileOrUrl as string);
    return;
  }

  const dataURL = await new Promise<string>((resolve, reject) => {
    const reader = new FileReader();

    reader.onload = function (event) {
      resolve(event.target?.result as string);
    };

    reader.onerror = function (error) {
      reject(error);
    };

    reader.readAsDataURL(fileOrUrl as File);
  });
  globalState.modal.attachments.addImageAttachment(dataURL);
};

export const scrollToBottom = () => {
  globalState.modal.chatContainer.scrollTop = globalState.modal.chatContainer.scrollHeight;
};
