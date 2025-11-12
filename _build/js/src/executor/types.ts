import type { UserAttachment, UserMessage, UserMessageContext } from '../chatHistory/types';

export type UsageData = {
  usage: {
    promptTokens: number;
    completionTokens: number;
  };
};

export type ToolCalls = {
  id: string;
  name: string;
  arguments: string;
}[];

export type TextDataAddon = never;

export type TextDataMaybeTools = Metadata &
  UsageData & {
    __type: 'TextDataMaybeTools';
    id: string;
    content: string;
    toolCalls?: ToolCalls;
    addons?: TextDataAddon[];
  };

export type TextDataWithTools = Metadata &
  UsageData & {
    __type: 'TextDataMaybeTools';
    id: string;
    content: string;
    toolCalls: ToolCalls;
    addons?: TextDataAddon[];
  };

export type ToolsData = Metadata &
  UsageData & {
    __type: 'ToolsData';
    id: string;
    content?: undefined;
    toolCalls: ToolCalls;
    addons?: TextDataAddon[];
  };

export type TextDataNoTools = Metadata &
  UsageData & {
    __type: 'TextDataNoTools';
    id: string;
    content: string;
    toolCalls?: undefined;
    addons?: TextDataAddon[];
  };

export type Metadata = {
  chatId?: number;
  metadata?: {
    model: string;
  };
};

export type TextData = TextDataNoTools | TextDataMaybeTools | ToolsData | TextDataWithTools;

export type ImageData = Metadata & {
  __type: 'ImageData';
  id: string;
  url: string;
};

export type ServiceHandler<CData, IData> = {
  content?: (data: CData) => TextData;
  image?: (data: IData) => ImageData;
};

export type ServiceResponse = TextData | ImageData;

export type StreamHandler = (
  chunk: string,
  buffer: string,
  currentData: TextData,
) => { buffer: string; currentData: TextData };

export type ForExecutor = {
  url: string;
  contentType: string;
  body: Record<string, unknown>;
  binary: Record<string, { base64: string; mimeType: string }[]>;
  service: string;
  model: string;
  headers: Record<string, string>;
  parser: string;
  stream: boolean;
};

export type ExecutorData =
  | {
      forExecutor: ForExecutor;
    }
  | string;

export type ToolResponseContent = {
  tool_call_id: string;
  name: string;
  response: string;
}[];

export type ChatParams = {
  persist?: boolean;
  chatId?: number;
  chatPublic?: boolean;
  userMsg?: UserMessage;
  lastMessageId?: string | null;
  field?: string;
  agent?: string;
  additionalOptions?: Record<string, unknown>;
  namespace?: string;
  messages: {
    role: 'user' | 'assistant' | 'tool';
    content?: string | ToolResponseContent;
    toolCalls?: ToolCalls;
    contexts?: UserMessageContext[];
    attachments?: UserAttachment[];
  }[];
};

export type TextParams = {
  field?: string;
  namespace?: string;
  additionalOptions?: Record<string, unknown>;
} & (
  | {
      resourceId: string | number;
    }
  | {
      content?: string;
    }
);

export type VisionParams = {
  field?: string;
  additionalOptions?: Record<string, unknown>;
  namespace?: string;
  image: string;
  resourceId?: string | number;
};

export type ImageParams = {
  persist?: boolean;
  chatId?: number;
  chatPublic?: boolean;
  userMsg?: UserMessage;
  lastMessageId?: string | null;
  additionalOptions?: Record<string, unknown>;
  field?: string;
  namespace?: string;
};

export type DownloadImageParams = {
  field?: string;
  namespace?: string;
  resource?: string | number;
  mediaSource?: string | number;
  path?: string;
  forceDownload?: boolean;
} & (
  | { url: string }
  | {
      messageId: string;
    }
);

export type ChunkStream<D = unknown> = (data: D) => void;

export const hasToolCalls = (data: TextData): data is TextDataWithTools | ToolsData => {
  return data.toolCalls !== undefined;
};
