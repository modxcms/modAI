import type { TextData, ImageData, ToolCalls } from './services';
import type { UserAttachment, UserMessageContext } from '../chatHistory';

export type ServiceResponse = TextData | ImageData;

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
