import {
  Metadata,
  ServiceResponse,
  TextDataWithTools,
  ToolCalls,
  ToolResponseContent,
  ToolsData,
} from '../executor/types';

export type AssistantMessageContentType = 'text' | 'image';

export type UpdatableHTMLElement<M extends Message = Message> = HTMLElement & {
  update?: (msg: M) => void;
  syncHeight?: () => void;
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

export type Config = {
  key: string;
  persist: boolean;
  tempId: string;
  chatId: number | undefined;
};

export type Store = {
  config: Record<string, Config>;
  history: {
    temp: Record<
      string,
      {
        title?: string;
        view_only: boolean;
        messages: Message[];
        idRef: Record<string, Message>;
      }
    >;
    chat: Record<
      number,
      {
        title?: string;
        view_only: boolean;
        messages: Message[];
        idRef: Record<string, Message>;
      }
    >;
  };
};

export type ChatHistory = {
  addUserMessage: (
    content:
      | string
      | { content: string; contexts?: UserMessageContext[]; attachments?: UserAttachment[] },
    hidden?: boolean,
  ) => UserMessage;
  addAssistantMessage: (data: ServiceResponse, hidden?: boolean) => Message;
  addToolCallsMessage: (toolCalls: TextDataWithTools | ToolsData, hidden?: boolean) => Message;
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
  clearHistoryFrom: (id: string) => Promise<void>;
  getKey: () => string;
  getLastMessageId: () => string | null;
  switchChatId: (chatId: number | undefined) => void;
  migrateTempChat: (chatId: number) => void;
  setTitle: (title: string) => void;
};
