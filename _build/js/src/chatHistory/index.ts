import { createOrUpdateChat, deleteChatByChatId, getChatId } from '../db';
import { executor } from '../executor';
import { isApiError } from '../executor/apiError';
import { globalState } from '../globalState';
import { renderMessage } from '../ui/localChat/messageHandlers';
import {
  hideWelcomeMessage,
  scrollToBottom,
  showWelcomeMessage,
} from '../ui/localChat/modalActions';
import { setPreloadingState } from '../ui/localChat/state';

import type {
  AssistantMessage,
  ChatHistory,
  Config,
  Message,
  Store,
  ToolResponseMessage,
  UpdatableHTMLElement,
  UserAttachment,
  UserMessage,
  UserMessageContext,
} from './types';
import type { ServiceResponse } from '../executor/types';

const clearHistory = (key: string) => {
  const cfg = getConfig(key);
  if (!cfg) {
    throw Error('Chat history not inited for this key.');
  }

  const history = getMessageStore(cfg);

  history.messages.forEach((msg) => {
    delete history.idRef[msg.id];
    msg.el?.remove();
  });

  history.messages = [];
};

const addUserMessage = (
  key: string,
  id: string,
  content:
    | string
    | { content: string; contexts?: UserMessageContext[]; attachments?: UserAttachment[] },
  hidden: boolean = false,
) => {
  const config = getConfig(key);
  if (!config) {
    throw Error('Chat history not inited for this key.');
  }

  const history = getMessageStore(config);

  const stringContent = typeof content === 'string';

  const msgObject: UserMessage = {
    __type: 'UserMessage',
    content: stringContent ? content : content.content,
    contexts: stringContent ? undefined : content.contexts,
    attachments: stringContent ? undefined : content.attachments,
    role: 'user',
    id,
    hidden,
    ctx: {},
  };

  history.messages.push(msgObject);
  history.idRef[id] = msgObject;

  msgObject.el = renderMessage(msgObject) as UpdatableHTMLElement | undefined;

  return msgObject;
};

const addToolResponseMessage = (
  key: string,
  id: string,
  content: ToolResponseMessage['content'],
  hidden: boolean = false,
) => {
  const config = getConfig(key);
  if (!config) {
    throw Error('Chat history not inited for this key.');
  }

  const history = getMessageStore(config);

  const msgObject: ToolResponseMessage = {
    __type: 'ToolResponseMessage',
    content,
    role: 'tool',
    id,
    hidden,
    ctx: {},
  };

  history.messages.push(msgObject);
  history.idRef[id] = msgObject;

  msgObject.el = renderMessage(msgObject) as UpdatableHTMLElement | undefined;

  return msgObject;
};

const addAssistantMessage = (key: string, data: ServiceResponse, hidden: boolean = false) => {
  const config = getConfig(key);
  if (!config) {
    throw Error('Chat history not inited for this key.');
  }

  const history = getMessageStore(config);

  const msgObject: AssistantMessage = {
    __type: 'AssistantMessage',
    content: undefined,
    toolCalls: undefined,
    contentType: 'text',
    role: 'assistant',
    id: data.id,
    metadata: data.metadata,
    hidden,
    ctx: {},
  };
  if (data.__type === 'ImageData') {
    msgObject.content = data.url;
    msgObject.contentType = 'image';
  } else {
    msgObject.content = data.content;
    msgObject.toolCalls = data.toolCalls;
  }

  history.messages.push(msgObject);
  history.idRef[data.id] = msgObject;

  msgObject.el = renderMessage(msgObject) as UpdatableHTMLElement | undefined;

  return msgObject;
};

const updateAssistantMessage = (key: string, data: ServiceResponse) => {
  const config = getConfig(key);
  if (!config) {
    throw Error('Chat history not inited for this key.');
  }

  const history = getMessageStore(config);

  if (!history.idRef[data.id]) {
    return addAssistantMessage(key, data, false);
  }

  const msg = history.idRef[data.id];

  if (data.__type === 'ImageData') {
    msg.content = data.url;
  } else {
    msg.content = data.content;
  }

  if (msg.__type === 'AssistantMessage' && msg.el && msg.el.update) {
    msg.el.update(msg);
  }

  return msg;
};

const getMessage = (key: string, id: string) => {
  const config = getConfig(key);
  if (!config) {
    throw Error('Chat history not inited for this key.');
  }

  const history = getMessageStore(config);

  return history.idRef[id];
};

const updateMessage = <M extends Message>(
  key: string,
  msg: M,
  newMessage: Partial<Omit<M, 'id' | '__type' | 'el'>>,
): M => {
  const config = getConfig(key);
  if (!config) {
    throw Error('Chat history not inited for this key.');
  }

  const history = getMessageStore(config);

  return { ...history.idRef[msg.id], ...newMessage } as M;
};

const loadFromDB = async (key: string) => {
  const chatId = await getChatId(key, globalState.config.user.id);

  if (chatId === null) {
    globalState.modal.chatId = undefined;
    globalState.modal.setTitle(undefined);
    showWelcomeMessage();
    return;
  }

  const config = getConfig(key);
  if (!config) {
    throw Error('Chat history not inited for this key.');
  }

  await loadMessages(config, chatId);
};

const switchChatId = async (chatId: number | undefined, key: string) => {
  const config = getConfig(key);
  if (!config) {
    throw Error('Chat history not inited for this key.');
  }

  if (chatId === undefined) {
    config.chatId = undefined;
    const history = getMessageStore(config);
    history.idRef = {};
    history.messages = [];

    return;
  }

  hideWelcomeMessage();

  await createOrUpdateChat(key, chatId, globalState.config.user.id);

  await loadMessages(config, chatId);
};

const loadMessages = async (config: Config, chatId: number) => {
  config.chatId = chatId;
  globalState.modal.chatId = chatId;

  const history = getMessageStore(config);
  if (history.messages.length > 0) {
    globalState.modal.setTitle(history.title);
    globalState.modal.actionButtons.forEach((btn) => {
      btn.enable();
    });

    history.messages.forEach((msg) => {
      if (msg.el) {
        msg.el.remove();
      }

      msg.el = renderMessage(msg) as UpdatableHTMLElement | undefined;
      if (msg.el) {
        msg.el.classList.remove('new');
        globalState.modal.chatMessages.appendChild(msg.el);
      }
    });

    scrollToBottom('instant');

    if (history.view_only) {
      globalState.modal.disableSending();
    } else {
      globalState.modal.enableSending();
    }

    return;
  }

  setPreloadingState(true);

  try {
    const data = await executor.chat.loadMessages(chatId);
    history.title = data.chat.title;
    history.view_only = data.chat.view_only;
    globalState.modal.setTitle(data.chat.title);

    for (const message of data.messages) {
      if (message.__type === 'UserMessage') {
        const msgObject: UserMessage = {
          init: true,
          __type: 'UserMessage',
          content: message.content as string,
          contexts: message.contexts,
          attachments: message.attachments,
          role: 'user',
          id: message.id,
          hidden: message.hidden,
          ctx: {},
        };

        history.messages.push(msgObject);
        history.idRef[message.id] = msgObject;

        msgObject.el = renderMessage(msgObject) as UpdatableHTMLElement | undefined;

        continue;
      }

      if (message.__type === 'AssistantMessage') {
        const msgObject: AssistantMessage = {
          init: true,
          __type: 'AssistantMessage',
          content: undefined,
          toolCalls: undefined,
          contentType: 'text',
          role: 'assistant',
          id: message.id,
          metadata: message.metadata,
          hidden: message.hidden,
          ctx: message.ctx,
          attachments: message.attachments,
          contexts: message.contexts,
        };

        if (message.contentType === 'image') {
          msgObject.content = message.content;
          msgObject.contentType = 'image';
        } else {
          msgObject.content = message.content;
          msgObject.toolCalls = message.toolCalls;
        }

        history.messages.push(msgObject);
        history.idRef[message.id] = msgObject;

        msgObject.el = renderMessage(msgObject) as UpdatableHTMLElement | undefined;
        continue;
      }

      if (message.__type === 'ToolResponseMessage') {
        const msgObject: ToolResponseMessage = {
          init: true,
          __type: 'ToolResponseMessage',
          content: message.content,
          role: 'tool',
          id: message.id,
          hidden: message.hidden,
          ctx: {},
        };

        history.messages.push(msgObject);
        history.idRef[message.id] = msgObject;

        msgObject.el = renderMessage(msgObject) as UpdatableHTMLElement | undefined;
      }
    }

    const messages = history.messages.filter((m) => !m.hidden);
    if (messages.length === 0) {
      showWelcomeMessage();
    }

    scrollToBottom('instant');
  } catch (err) {
    if (isApiError(err)) {
      if (err.statusCode === 404) {
        void deleteChatByChatId(chatId);
        globalState.modal.chatId = undefined;
      }
    }
  }

  setPreloadingState(false);

  if (history.view_only) {
    globalState.modal.disableSending();
  } else {
    globalState.modal.enableSending();
  }
};

const store: Store = {
  config: {},
  history: {
    temp: {},
    chat: {},
  },
};

const getMessageStore = (config: Config) => {
  if (config.chatId) {
    return getChatMessageStore(config.chatId);
  }

  if (config.persist) {
    return getTempMessageStore(config.tempId);
  }

  return getTempMessageStore(config.key);
};

const getChatMessageStore = (chatId: number) => {
  if (store.history.chat[chatId]) {
    return store.history.chat[chatId];
  }

  store.history.chat[chatId] = {
    messages: [],
    idRef: {},
    view_only: false,
  };

  return store.history.chat[chatId];
};

const getTempMessageStore = (key: string) => {
  if (store.history.temp[key]) {
    return store.history.temp[key];
  }

  store.history.temp[key] = {
    messages: [],
    idRef: {},
    view_only: false,
  };

  return store.history.temp[key];
};

const getConfig = (key: string) => {
  if (store.config[key]) {
    return store.config[key];
  }

  return null;
};

export const chatHistory = {
  init: (config: { key: string; persist?: boolean }): ChatHistory => {
    if (!store.config[config.key]) {
      store.config[config.key] = {
        key: config.key,
        persist: config.persist ?? false,
        tempId: window.crypto.randomUUID(),
        chatId: undefined,
      };
    }

    if (config.persist) {
      void loadFromDB(config.key);
    }

    hideWelcomeMessage();

    return {
      addUserMessage: (content, hidden = false) => {
        const id = window.crypto.randomUUID();
        return addUserMessage(config.key, id, content, hidden);
      },
      addAssistantMessage: (data, hidden = false) => {
        return addAssistantMessage(config.key, data, hidden);
      },

      addToolCallsMessage: (data, hidden = false) => {
        return addAssistantMessage(
          config.key,
          {
            __type: 'ToolsData',
            id: data.id,
            content: undefined,
            toolCalls: data.toolCalls,
            usage: data.usage,
            metadata: data.metadata,
          },
          hidden,
        );
      },
      addToolResponseMessage: (id, content, hidden = false) => {
        return addToolResponseMessage(config.key, id, content, hidden);
      },
      updateAssistantMessage: (data) => {
        return updateAssistantMessage(config.key, data);
      },
      updateMessage: (msg, newMessage) => {
        return updateMessage(config.key, msg, newMessage);
      },
      getAssistantMessage: (id) => {
        return getMessage(config.key, id);
      },
      getMessages: () => {
        const cfg = getConfig(config.key);
        if (!cfg) {
          throw Error('Chat history not inited for this key.');
        }

        const history = getMessageStore(cfg);
        return history.messages;
      },
      getMessagesHistory: () => {
        const cfg = getConfig(config.key);
        if (!cfg) {
          throw Error('Chat history not inited for this key.');
        }

        const history = getMessageStore(cfg);

        return history.messages.map((m) => ({
          role: m.role,
          content: m.content,
          toolCalls: m.toolCalls,
          contexts: m.contexts,
          attachments: m.attachments,
        }));
      },
      clearHistory: () => {
        clearHistory(config.key);
      },
      clearHistoryFrom: async (id: string) => {
        const cfg = getConfig(config.key);
        if (!cfg) {
          throw Error('Chat history not inited for this key.');
        }

        const history = getMessageStore(cfg);

        const startIndex = history.messages.findIndex((obj) => obj.id === id);

        if (startIndex !== -1) {
          for (let i = startIndex; i < history.messages.length; i++) {
            const obj = history.messages[i];
            obj.el?.remove();
          }

          history.messages.splice(startIndex);
        }

        if (cfg.persist && cfg.chatId) {
          await executor.chat.deleteMessages({
            chatId: cfg.chatId,
            fromMessageId: id,
          });
        }
      },
      getKey: () => {
        return config.key;
      },
      getLastMessageId: () => {
        const cfg = getConfig(config.key);
        if (!cfg) {
          throw Error('Chat history not inited for this key.');
        }

        const history = getMessageStore(cfg);

        const len = history.messages.length;
        if (len === 0) {
          return null;
        }

        return history.messages[len - 1].id;
      },
      switchChatId: (chatId: number | undefined) => {
        void switchChatId(chatId, config.key);
      },
      migrateTempChat: (chatId: number) => {
        const tempId = store.config[config.key].tempId;
        store.config[config.key].chatId = chatId;

        store.history.chat[chatId] = store.history.temp[tempId];
        delete store.history.temp[tempId];
      },
      setTitle: (title: string) => {
        const cfg = getConfig(config.key);
        if (!cfg) {
          throw Error('Chat history not inited for this key.');
        }

        if (cfg.chatId) {
          store.history.chat[cfg.chatId].title = title;
        } else {
          store.history.temp[cfg.tempId].title = title;
        }
      },
    };
  },
};
