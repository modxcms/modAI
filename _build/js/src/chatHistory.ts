import {
  saveMessage,
  deleteAllMessages,
  deleteMessagesAfter,
  getMessages,
  updateMessage as updateMessageInDB,
} from './db';

import type { ToolResponseContent, ToolCalls, ServiceResponse, Metadata } from './executor/types';

export type AssistantMessageContentType = 'text' | 'image';

export type UpdatableHTMLElement<M extends Message = Message> = HTMLElement & {
  update?: (msg: M) => void;
};

export type UserMessageContext = {
  __type: string;
  name: string;
  renderer?: string;
  value: string;
};

export type UserAttachment = {
  __type: 'image';
  value: string;
};

export type BaseMessage = {
  id: string;
  hidden: boolean;
  ctx: Record<string, unknown>;
  toolCalls?: undefined;
  contexts?: undefined;
  attachments?: undefined;
  init?: boolean | undefined;
};

export type ToolResponseMessage = BaseMessage & {
  __type: 'ToolResponseMessage';
  role: 'tool';
  content: ToolResponseContent;
  el?: UpdatableHTMLElement<ToolResponseMessage>;
};

export type AssistantMessage = Metadata &
  Omit<BaseMessage, 'toolCalls'> & {
    __type: 'AssistantMessage';
    role: 'assistant';
    content: string | undefined;
    contentType: AssistantMessageContentType;
    toolCalls?: ToolCalls;
    el?: UpdatableHTMLElement<AssistantMessage>;
  };

export type UserMessage = Omit<BaseMessage, 'contexts' | 'attachments'> & {
  __type: 'UserMessage';
  role: 'user';
  content: string;
  contexts?: UserMessageContext[];
  attachments?: UserAttachment[];
  el?: UpdatableHTMLElement<UserMessage>;
};

export type Message = UserMessage | AssistantMessage | ToolResponseMessage;

type Namespace = {
  history: Message[];
  idRef: Record<string, Message>;
  persist: boolean;
  onAddMessage: <M extends Message = Message>(msg: M) => UpdatableHTMLElement<M> | undefined;
};

const _namespace: Record<string, Namespace> = {};

const addUserMessage = (
  key: string,
  id: string,
  content:
    | string
    | { content: string; contexts?: UserMessageContext[]; attachments?: UserAttachment[] },
  hidden: boolean = false,
) => {
  const namespace = _namespace[key];
  if (!namespace) {
    throw Error("Namespace doesn't exist.");
  }

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

  const index = namespace.history.push(msgObject) - 1;
  if (id) {
    namespace.idRef[id] = namespace.history[index];
  }

  msgObject.el = namespace.onAddMessage(msgObject);

  if (namespace.persist) {
    void saveMessage(key, msgObject);
  }

  return msgObject;
};

const addToolResponseMessage = (
  key: string,
  id: string,
  content: ToolResponseMessage['content'],
  hidden: boolean = false,
) => {
  const namespace = _namespace[key];
  if (!namespace) {
    throw Error("Namespace doesn't exist.");
  }

  const msgObject: ToolResponseMessage = {
    __type: 'ToolResponseMessage',
    content,
    role: 'tool',
    id,
    hidden,
    ctx: {},
  };

  const index = namespace.history.push(msgObject) - 1;
  if (id) {
    namespace.idRef[id] = namespace.history[index];
  }

  msgObject.el = namespace.onAddMessage(msgObject);

  if (namespace.persist) {
    void saveMessage(key, msgObject);
  }

  return msgObject;
};

const addAssistantMessage = (key: string, data: ServiceResponse, hidden: boolean = false) => {
  const namespace = _namespace[key];
  if (!namespace) {
    throw Error("Namespace doesn't exist.");
  }

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

  const index = namespace.history.push(msgObject) - 1;
  if (data.id) {
    namespace.idRef[data.id] = namespace.history[index];
  }

  msgObject.el = namespace.onAddMessage(msgObject);

  if (namespace.persist) {
    void saveMessage(key, msgObject);
  }

  return msgObject;
};

const updateAssistantMessage = (key: string, data: ServiceResponse) => {
  const namespace = _namespace[key];
  if (!namespace) {
    throw Error("Namespace doesn't exist.");
  }

  if (!namespace.idRef[data.id]) {
    return addAssistantMessage(key, data, false);
  }

  const msg = namespace.idRef[data.id];

  if (data.__type === 'ImageData') {
    msg.content = data.url;
  } else {
    msg.content = data.content;
  }

  if (msg.__type === 'AssistantMessage' && msg.el && msg.el.update) {
    msg.el.update(msg);
  }

  if (namespace.persist) {
    void updateMessageInDB(msg);
  }

  return msg;
};

const getMessage = (key: string, id: string) => {
  const namespace = _namespace[key];
  if (!namespace) {
    throw Error("Namespace doesn't exist.");
  }

  return namespace.idRef[id];
};

const updateMessage = <M extends Message>(
  key: string,
  msg: M,
  newMessage: Partial<Omit<M, 'id' | '__type' | 'el'>>,
): M => {
  const namespace = _namespace[key];
  if (!namespace) {
    throw Error("Namespace doesn't exist.");
  }

  const message = { ...namespace.idRef[msg.id], ...newMessage } as M;

  if (namespace.persist) {
    void updateMessageInDB(msg);
  }

  return message;
};

const loadFromDB = async (key: string, onInitDone?: () => void) => {
  const messages = await getMessages(key);
  for (const message of messages) {
    const namespace = _namespace[message.category];
    if (!namespace) {
      continue;
    }

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

      const index = namespace.history.push(msgObject) - 1;
      namespace.idRef[message.id] = namespace.history[index];

      msgObject.el = namespace.onAddMessage(msgObject);

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

      const index = namespace.history.push(msgObject) - 1;
      namespace.idRef[message.id] = namespace.history[index];

      msgObject.el = namespace.onAddMessage(msgObject);
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

      const index = namespace.history.push(msgObject) - 1;
      if (message.id) {
        namespace.idRef[message.id] = namespace.history[index];
      }

      msgObject.el = namespace.onAddMessage(msgObject);
    }
  }

  if (onInitDone) {
    onInitDone();
  }
};

export type ChatHistory = {
  addUserMessage: (
    content:
      | string
      | { content: string; contexts?: UserMessageContext[]; attachments?: UserAttachment[] },
    hidden?: boolean,
  ) => Message;
  addAssistantMessage: (data: ServiceResponse, hidden?: boolean) => Message;
  addToolCallsMessage: (toolCalls: ToolCalls, hidden?: boolean) => Message;
  addToolResponseMessage: (
    id: string,
    content: ToolResponseMessage['content'],
    hidden?: boolean,
  ) => Message;
  updateAssistantMessage: (data: ServiceResponse) => Message;
  updateMessage: <M extends Message>(
    msg: M,
    newMessage: Partial<Omit<M, 'id' | '__type' | 'el'>>,
  ) => M;
  getAssistantMessage: (id: string) => Message | undefined;
  getMessages: () => Message[];
  getMessagesHistory: () => Pick<
    Message,
    'role' | 'content' | 'toolCalls' | 'contexts' | 'attachments'
  >[];
  clearHistory: () => void;
  clearHistoryFrom: (id: string) => void;
};

export const chatHistory = {
  init: (config: {
    key: string;
    persist?: boolean;
    onAddMessage: Namespace['onAddMessage'];
    onInitDone?: () => void;
  }): ChatHistory => {
    if (!_namespace[config.key]) {
      _namespace[config.key] = {
        history: [],
        idRef: {},
        persist: config.persist ?? false,
        onAddMessage: config.onAddMessage,
      };

      if (config.persist) {
        void loadFromDB(config.key, config.onInitDone);
      }
    }

    if (config.onAddMessage) {
      _namespace[config.key].onAddMessage = config.onAddMessage;
    }

    return {
      addUserMessage: (content, hidden = false) => {
        const id = 'user-msg-' + Date.now() + Math.round(Math.random() * 1000);
        return addUserMessage(config.key, id, content, hidden);
      },
      addAssistantMessage: (data, hidden = false) => {
        return addAssistantMessage(config.key, data, hidden);
      },

      addToolCallsMessage: (toolCalls, hidden = false) => {
        return addAssistantMessage(
          config.key,
          {
            __type: 'ToolsData',
            id: crypto.randomUUID(),
            content: undefined,
            toolCalls,
            usage: {
              completionTokens: 0,
              promptTokens: 0,
            },
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
        return _namespace[config.key].history;
      },
      getMessagesHistory: () => {
        return _namespace[config.key].history.map((m) => ({
          role: m.role,
          content: m.content,
          toolCalls: m.toolCalls,
          contexts: m.contexts,
          attachments: m.attachments,
        }));
      },
      clearHistory: () => {
        _namespace[config.key].history.forEach((msg) => {
          msg.el?.remove();
        });
        _namespace[config.key].history = [];

        void deleteAllMessages(config.key);
      },
      clearHistoryFrom: (id: string) => {
        const startIndex = _namespace[config.key].history.findIndex((obj) => obj.id === id);

        if (startIndex !== -1) {
          for (let i = startIndex; i < _namespace[config.key].history.length; i++) {
            const obj = _namespace[config.key].history[i];
            obj.el?.remove();
          }

          _namespace[config.key].history.splice(startIndex);
        }

        void deleteMessagesAfter(config.key, id);
      },
    };
  },
};
