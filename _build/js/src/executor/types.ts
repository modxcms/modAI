import type { UserAttachment, UserMessageContext } from '../chatHistory';

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

export type TextDataMaybeTools = UsageData & {
  __type: 'TextDataMaybeTools';
  id: string;
  content: string;
  toolCalls?: ToolCalls;
};

export type ToolsData = UsageData & {
  __type: 'ToolsData';
  id: string;
  content?: undefined;
  toolCalls: ToolCalls;
};

export type TextDataNoTools = UsageData & {
  __type: 'TextDataNoTools';
  id: string;
  content: string;
  toolCalls?: undefined;
};

export type TextData = TextDataNoTools | TextDataMaybeTools | ToolsData;

export type ImageData = {
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
  onChunkStream?: ChunkStream<TextData>,
) => { buffer: string; currentData: TextData };

export type ForExecutor = {
  url: string;
  body: string;
  service: string;
  headers: HeadersInit;
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
  prompt: string;
  field?: string;
  agent?: string;
  contexts?: UserMessageContext[];
  attachments?: UserAttachment[];
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
  namespace?: string;
  image: string;
};

export type ImageParams = {
  prompt: string;
  field?: string;
  namespace?: string;
};

export type DownloadImageParams = {
  url: string;
  field?: string;
  namespace?: string;
  resource?: string | number;
  mediaSource?: string | number;
};

export type ChunkStream<D = unknown> = (data: D) => void;
