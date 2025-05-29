import { drag, endDrag } from './dragHandlers';
import { addErrorMessage, renderMessage } from './messageHandlers';
import { setLoadingState } from './state';
import { chatHistory } from '../../chatHistory';
import { executor } from '../../executor';
import { globalState } from '../../globalState';
import { lng } from '../../lng';

import type { LocalChatConfig, ModalType } from './types';
import type { UpdatableHTMLElement } from '../../chatHistory';
import type { ToolCalls } from '../../executor/types';

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

const callTools = async (
  config: LocalChatConfig,
  toolCalls: ToolCalls,
  agent?: string,
  additionalOptions?: Record<string, unknown>,
  controller?: AbortController,
) => {
  globalState.modal.history.addToolCallsMessage(toolCalls, true);

  const res = await executor.tools.run({ toolCalls, agent }, controller);

  globalState.modal.history.addToolResponseMessage(res.id, res.content, true);

  const aiRes = await executor.prompt.chat(
    {
      namespace: config.namespace,
      additionalOptions,
      agent,
      field: config.field || '',
      prompt: '',
      messages: globalState.modal.history.getMessagesHistory(),
    },
    (data) => {
      if (data.__type === 'TextDataNoTools' || data.__type === 'TextDataMaybeTools') {
        globalState.modal.history.updateAssistantMessage(data);
      }
    },
    controller,
  );

  if (aiRes.content) {
    globalState.modal.history.updateAssistantMessage(aiRes);
  }

  if (aiRes.toolCalls) {
    await callTools(
      config,
      aiRes.toolCalls,
      agent,
      additionalOptions,
      globalState.modal.abortController,
    );
  }
};

export const sendMessage = async (
  config: LocalChatConfig,
  providedMessage?: string,
  hidePrompt?: boolean,
) => {
  const message = providedMessage
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

  const attachments =
    globalState.modal.attachments.attachments.length > 0
      ? globalState.modal.attachments.attachments.map((at) => ({
          __type: at.__type,
          value: at.value,
        }))
      : undefined;

  const contexts =
    globalState.modal.context.contexts.length > 0
      ? globalState.modal.context.contexts.map((at) => ({
          __type: at.__type,
          name: at.name,
          renderer: at.renderer,
          value: at.value,
        }))
      : [];

  globalState.modal.attachments.removeAttachments();
  globalState.modal.context.removeContexts();

  const messages = globalState.modal.history.getMessagesHistory();
  const userMsg = globalState.modal.history.addUserMessage(
    { content: message, attachments, contexts },
    hidePrompt,
  );

  const selectedAgent = globalState.selectedAgent[`${config.key}/${config.type}`];

  if (
    selectedAgent &&
    selectedAgent.contextProviders &&
    selectedAgent.contextProviders.length > 0
  ) {
    const remoteContexts = await executor.context.get({
      prompt: message,
      agent: selectedAgent.name,
    });
    remoteContexts.contexts.map((ctx) => {
      contexts.push({
        __type: 'ContextProvider',
        name: 'ContextProvider',
        renderer: undefined,
        value: ctx,
      });
    });
  }

  globalState.modal.history.updateMessage(userMsg, { contexts });

  const additionalOptions = Object.entries(
    globalState.additionalControls[`${config.key}/${config.type}`] ?? {},
  ).reduce(
    (acc, [key, item]) => {
      if (!item) {
        return acc;
      }

      acc[key] = item['value'];

      return acc;
    },
    {} as Record<string, unknown>,
  );

  try {
    if (config.type === 'text') {
      const data = await executor.prompt.chat(
        {
          agent: selectedAgent?.name,
          additionalOptions,
          namespace: config.namespace,
          contexts: contexts,
          attachments: attachments,
          prompt: message,
          field: config.field || '',
          messages,
        },
        (data) => {
          if (data.content) {
            globalState.modal.history.updateAssistantMessage(data);
          }
        },
        globalState.modal.abortController,
      );

      if (data.content) {
        globalState.modal.history.updateAssistantMessage(data);
      }

      if (data.toolCalls) {
        await callTools(
          config,
          data.toolCalls,
          selectedAgent?.name,
          additionalOptions,
          globalState.modal.abortController,
        );
      }
    }

    if (config.type === 'image') {
      const data = await executor.prompt.image(
        {
          prompt: message,
          additionalOptions,
          attachments: attachments,
        },
        globalState.modal.abortController,
      );

      globalState.modal.history.addAssistantMessage(data);
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

    addErrorMessage(lng('modai.error.unknown_error'));
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

  globalState.modal.history = chatHistory.init({
    key: `${config.namespace ?? 'modai'}/${config.key}/${config.type}`,
    persist: config.persist,
    onAddMessage: (msg) => {
      return renderMessage(msg, config) as UpdatableHTMLElement | undefined;
    },
    onInitDone: () => {
      const messages = globalState.modal.history.getMessages().filter((m) => !m.hidden);
      if (messages.length > 0) {
        globalState.modal.welcomeMessage.style.display = 'none';

        globalState.modal.actionButtons.forEach((btn) => {
          btn.enable();
        });
      }
    },
  });

  while (globalState.modal.chatMessages.firstChild) {
    globalState.modal.chatMessages.removeChild(globalState.modal.chatMessages.firstChild);
  }

  const messages = globalState.modal.history.getMessages().filter((m) => !m.hidden);
  if (messages.length > 0) {
    globalState.modal.welcomeMessage.style.display = 'none';

    messages.forEach((msg) => {
      if (msg.el) {
        msg.el.classList.remove('new');
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

  globalState.modal.reloadChatControls();

  scrollToBottom('instant');
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
    addErrorMessage(lng('modai.error.only_image_files_are_allowed'));
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

export const scrollToBottom = (behavior: 'smooth' | 'instant' = 'smooth') => {
  globalState.modal.chatContainer.scrollTo({
    top: globalState.modal.chatContainer.scrollHeight,
    behavior: behavior,
  });
};
