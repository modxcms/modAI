import type { ToolCalls } from './executor/services';
import type { Prompt, ToolResponseContent } from './executor/types';

export type AssistantMessageContentType = 'text' | 'image';

export type UpdatableHTMLElement<M extends Message = Message> = HTMLElement & {
  update?: (msg: M) => void;
};

type BaseMessage = {
  id: string;
  hidden: boolean;
  ctx: Record<string, unknown>;
  toolCalls?: undefined;
};

export type ToolResponseMessage = BaseMessage & {
  __type: 'ToolResponseMessage';
  role: 'tool';
  content: ToolResponseContent;
  el?: UpdatableHTMLElement<ToolResponseMessage>;
};

export type AssistantMessage = Omit<BaseMessage, 'toolCalls'> & {
  __type: 'AssistantMessage';
  role: 'assistant';
  content: string | null | undefined;
  contentType: AssistantMessageContentType;
  toolCalls?: ToolCalls;
  el?: UpdatableHTMLElement<AssistantMessage>;
};

export type UserMessage = BaseMessage & {
  __type: 'UserMessage';
  role: 'user';
  content: Prompt;
  el?: UpdatableHTMLElement<UserMessage>;
};

export type Message = UserMessage | AssistantMessage | ToolResponseMessage;

type Namespace = {
  history: Message[];
  idRef: Record<string, Message>;
  onAddMessage: <M extends Message = Message>(msg: M) => UpdatableHTMLElement<M> | undefined;
};

const _namespace: Record<string, Namespace> = {};

const addUserMessage = (key: string, id: string, content: Prompt, hidden: boolean = false) => {
  const namespace = _namespace[key];
  if (!namespace) {
    return;
  }

  const msgObject: UserMessage = {
    __type: 'UserMessage',
    content,
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
};

const addToolResponseMessage = (
  key: string,
  id: string,
  content: ToolResponseMessage['content'],
  hidden: boolean = false,
) => {
  const namespace = _namespace[key];
  if (!namespace) {
    return;
  }

  const msgObject: Message = {
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
};

const addAssistantMessage = (
  key: string,
  id: string,
  content: string | null | undefined,
  toolCalls: ToolCalls | undefined,
  contentType: AssistantMessageContentType,
  hidden: boolean = false,
) => {
  const namespace = _namespace[key];
  if (!namespace) {
    return;
  }

  const msgObject: Message = {
    __type: 'AssistantMessage',
    content,
    toolCalls,
    contentType,
    role: 'assistant',
    id,
    hidden,
    ctx: {},
  };

  const index = namespace.history.push(msgObject) - 1;
  if (id) {
    namespace.idRef[id] = namespace.history[index];
  }

  msgObject.el = namespace.onAddMessage(msgObject);
};

const updateAssistantMessage = (key: string, id: string, content: string) => {
  const namespace = _namespace[key];
  if (!namespace) {
    return;
  }

  if (!namespace.idRef[id]) {
    addAssistantMessage(key, id, content, undefined, 'text', false);
    return;
  }

  const msg = namespace.idRef[id];
  msg.content = content;

  if (msg.__type === 'AssistantMessage' && msg.el && msg.el.update) {
    msg.el.update(msg);
  }
};

const getMessage = (key: string, id: string) => {
  const namespace = _namespace[key];
  if (!namespace) {
    return;
  }

  return namespace.idRef[id];
};

export type ChatHistory = {
  addUserMessage: (content: Prompt, hidden?: boolean) => void;
  addAssistantMessage: (
    id: string,
    content: string | null | undefined,
    toolCalls: ToolCalls | undefined,
    contentType: AssistantMessageContentType,
    hidden?: boolean,
  ) => void;
  addToolResponseMessage: (
    id: string,
    content: ToolResponseMessage['content'],
    hidden?: boolean,
  ) => void;
  updateAssistantMessage: (id: string, content: string) => void;
  getAssistantMessage: (id: string) => Message | undefined;
  getMessages: () => Message[];
  getMessagesHistory: () => Pick<Message, 'role' | 'content' | 'toolCalls'>[];
  clearHistory: () => void;
  clearHistoryFrom: (id: string) => void;
};

export const chatHistory = {
  init: (key: string, onAddMessage: Namespace['onAddMessage']): ChatHistory => {
    if (!_namespace[key]) {
      _namespace[key] = {
        history: [],
        idRef: {},
        onAddMessage,
      };
    }

    _namespace[key].onAddMessage = onAddMessage;

    return {
      addUserMessage: (content: Prompt, hidden = false) => {
        const id = 'user-msg-' + Date.now() + Math.round(Math.random() * 1000);
        addUserMessage(key, id, content, hidden);
      },
      addAssistantMessage: (id, content, toolCalls, contentType, hidden = false) => {
        addAssistantMessage(key, id, content, toolCalls, contentType, hidden);
      },
      addToolResponseMessage: (id, content, hidden = false) => {
        addToolResponseMessage(key, id, content, hidden);
      },
      updateAssistantMessage: (id, content) => {
        updateAssistantMessage(key, id, content);
      },
      getAssistantMessage: (id) => {
        return getMessage(key, id);
      },
      getMessages: () => {
        return _namespace[key].history;
      },
      getMessagesHistory: () => {
        return _namespace[key].history.map((m) => ({
          role: m.role,
          content: m.content,
          toolCalls: m.toolCalls,
        }));
      },
      clearHistory: () => {
        _namespace[key].history.forEach((msg) => {
          msg.el?.remove();
        });
        _namespace[key].history = [];
      },
      clearHistoryFrom: (id: string) => {
        const startIndex = _namespace[key].history.findIndex((obj) => obj.id === id);

        if (startIndex !== -1) {
          for (let i = startIndex; i < _namespace[key].history.length; i++) {
            const obj = _namespace[key].history[i];
            obj.el?.remove();
          }

          _namespace[key].history.splice(startIndex);
        }
      },
    };
  },
};
