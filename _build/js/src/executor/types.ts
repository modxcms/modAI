import { TextData, ImageData } from './services';

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

export type TextPrompt = { type: 'text'; value: string };
export type ImagePrompt = { type: 'image'; value: string };

export type ToolResponseContent = {
  tool_call_id: string;
  name: string;
  response: string;
}[];

export type Prompt = string | [TextPrompt, ...ImagePrompt[]];

export type HistoryMessage = undefined | null | Prompt | ToolResponseContent;

export type FreeTextParams = {
  prompt: Prompt;
  field?: string;
  context?: string;
  namespace?: string;
  messages: { role: string; content: HistoryMessage }[];
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
