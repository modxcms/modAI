import { drag, endDrag } from './dragHandlers';
import { addErrorMessage } from './messageHandlers';
import { setLoadingState } from './state';
import { chatHistory } from '../../chatHistory';
import { chats } from '../../chats';
import { createOrUpdateChat } from '../../db';
import { executor } from '../../executor';
import { hasToolCalls, TextDataWithTools, ToolsData } from '../../executor/types';
import { globalState } from '../../globalState';
import { lng } from '../../lng';

import type { LocalChatConfig, ModalType } from './types';

export const closeModal = () => {
  if (globalState.modal.isLoading) {
    return;
  }

  document.removeEventListener('mousemove', (e) => drag(e));
  document.removeEventListener('mouseup', () => endDrag());

  if (globalState.modal) {
    globalState.modal.remove();
  }

  chats.clearFilters();

  globalState.modalOpen = false;
};

const callTools = async (
  config: LocalChatConfig,
  data: TextDataWithTools | ToolsData,
  agent?: string,
  additionalOptions?: Record<string, unknown>,
  controller?: AbortController,
) => {
  const toolCallMsg = globalState.modal.history.addToolCallsMessage(data, true);
  if (globalState.modal.chatId) {
    await executor.chat.storeMessage(
      globalState.modal.chatId,
      toolCallMsg,
      !data.content ? data.usage : undefined, // if content is present, usage is logged with the content message
    );
  }

  const res = await executor.tools.run(
    { toolCalls: data.toolCalls, agent, chatId: globalState.modal.chatId },
    controller,
  );

  globalState.modal.history.addToolResponseMessage(res.id, res.content, true);

  const aiRes = await executor.prompt.chat(
    {
      namespace: config.namespace,
      additionalOptions,
      agent,
      field: config.field || '',
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
    const assistantMsg = globalState.modal.history.updateAssistantMessage(aiRes);

    if (globalState.modal.chatId) {
      await executor.chat.storeMessage(globalState.modal.chatId, assistantMsg, aiRes.usage);
    }
  }

  if (hasToolCalls(aiRes)) {
    await callTools(config, aiRes, agent, additionalOptions, globalState.modal.abortController);
  }
};

export const sendMessage = async (providedMessage?: string, hidePrompt?: boolean) => {
  const config = globalState.modal.config;
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
  try {
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

    const lastMessageId = globalState.modal.history.getLastMessageId();

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
    let titleDataPromise = null;

    if (globalState.config.generateChatTitle && !lastMessageId) {
      titleDataPromise = executor.prompt
        .chatTitle({
          message: userMsg.content,
        })
        .then((title) => {
          globalState.modal.setTitle(title.content);
          return title;
        });
    }

    if (config.type === 'text') {
      const data = await executor.prompt.chat(
        {
          persist: config.persist,
          chatId: globalState.modal.chatId,
          chatPublic: globalState.modal.chatPublic,
          lastMessageId,
          userMsg: userMsg,
          agent: selectedAgent?.name,
          additionalOptions,
          namespace: config.namespace,
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

      const returnedChatId = data.chatId;

      const currentChatId = globalState.modal.chatId || returnedChatId;
      if (titleDataPromise !== null && currentChatId) {
        titleDataPromise?.then((title) => {
          if (title.content) {
            void executor.chat.setChatTitle(currentChatId, title.content);
            globalState.modal.history.setTitle(title.content);
          }
        });

        chats.markAsStale();
      }

      if (!globalState.modal.chatId && returnedChatId) {
        chats.markAsStale();
        globalState.modal.chatId = returnedChatId;

        void createOrUpdateChat(
          globalState.modal.history.getKey(),
          returnedChatId,
          globalState.config.user.id,
        );

        globalState.modal.history.migrateTempChat(returnedChatId);
      }

      if (data.content) {
        const assistantMsg = globalState.modal.history.updateAssistantMessage(data);
        if (globalState.modal.chatId) {
          await executor.chat.storeMessage(globalState.modal.chatId, assistantMsg, data.usage);
        }
      }

      if (hasToolCalls(data)) {
        await callTools(
          config,
          data,
          selectedAgent?.name,
          additionalOptions,
          globalState.modal.abortController,
        );
      }
    }

    if (config.type === 'image') {
      const data = await executor.prompt.image(
        {
          persist: config.persist,
          chatId: globalState.modal.chatId,
          chatPublic: globalState.modal.chatPublic,
          lastMessageId,
          userMsg: userMsg,
          additionalOptions,
        },
        globalState.modal.abortController,
      );

      const returnedChatId = data.chatId;

      const currentChatId = globalState.modal.chatId || returnedChatId;
      if (titleDataPromise !== null && currentChatId) {
        titleDataPromise?.then((title) => {
          if (title.content) {
            void executor.chat.setChatTitle(currentChatId, title.content);
            globalState.modal.history.setTitle(title.content);
          }
        });

        chats.markAsStale();
      }

      if (!globalState.modal.chatId && returnedChatId) {
        chats.markAsStale();
        globalState.modal.chatId = returnedChatId;

        void createOrUpdateChat(
          globalState.modal.history.getKey(),
          returnedChatId,
          globalState.config.user.id,
        );

        globalState.modal.history.migrateTempChat(returnedChatId);
      }

      const assistantMsg = globalState.modal.history.addAssistantMessage(data);

      if (globalState.modal.chatId) {
        await executor.chat.storeMessage(globalState.modal.chatId, assistantMsg);
      }
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

export const switchType = (type: ModalType) => {
  const config = globalState.modal.config;

  config.type = type;

  globalState.modal.history = chatHistory.init({
    key: `${config.namespace ?? 'modai'}/${config.key}/${config.type}`,
    persist: config.persist,
  });

  globalState.modal.reloadChatControls();

  scrollToBottom('instant');
};

export const clearChat = () => {
  globalState.modal.history.clearHistory();
  globalState.modal.setTitle(undefined);
  showWelcomeMessage();
  disableActionButtons();
};

export const showWelcomeMessage = () => {
  globalState.modal.chatMessages.innerHTML = '';
  globalState.modal.welcomeMessage.style.display = 'block';
};

export const hideWelcomeMessage = () => {
  globalState.modal.chatMessages.innerHTML = '';
  globalState.modal.welcomeMessage.style.display = 'none';
};

export const disableActionButtons = () => {
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
