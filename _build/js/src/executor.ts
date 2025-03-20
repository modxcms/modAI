import { globalState } from './globalState';
import { lng } from './lng';

type ServiceType = 'chatgpt' | 'claude' | 'gemini';
type BufferMode = 'buffered' | 'stream';

export type ServiceResponse = TextData | ImageData;

type ChatGPTCompletionsData = {
  id: string;
  choices?: {
    message?: {
      content: string;
    };
  }[];
};

type ChatGPTStreamCompletionsData = {
  id: string;
  choices?: {
    delta?: {
      content: string;
    };
  }[];
};

type ChatGPTImageData = {
  data?: {
    url?: string;
    b64_json?: string;
  }[];
};

type ClaudeCompletionsData = {
  id: string;
  content?: {
    text?: string;
  }[];
};

type ClaudeStreamCompletionsData = {
  id: string;
  delta?: {
    text?: string;
  };
};

type GeminiCompletionsData = {
  candidates?: {
    content?: {
      parts?: {
        text?: string;
      }[];
    };
  }[];
};

type GeminiStreamCompletionsData = {
  candidates?: {
    content?: {
      parts?: {
        text?: string;
      }[];
    };
  }[];
};

type GeminiImageData = {
  predictions?: {
    bytesBase64Encoded?: string;
  }[];
};

type ServiceHandlers = {
  buffered: {
    chatgpt: {
      content: (data: ChatGPTCompletionsData) => TextData;
      image: (data: ChatGPTImageData) => ImageData;
    };
    claude: {
      content: (data: ClaudeCompletionsData) => TextData;
    };
    gemini: {
      content: (data: GeminiCompletionsData) => TextData;
      image: (data: GeminiImageData) => ImageData;
    };
  };
  stream: {
    chatgpt: {
      content: (newData: ChatGPTStreamCompletionsData, currentData: TextData) => TextData;
    };
    claude: {
      content: (newData: ClaudeStreamCompletionsData, currentData: TextData) => TextData;
    };
    gemini: {
      content: (newData: GeminiStreamCompletionsData, currentData: TextData) => TextData;
    };
  };
};

type ForExecutor = {
  url: string;
  body: string;
  service: string;
  headers: HeadersInit;
  parser: string;
  stream: boolean;
};

type ExecutorData =
  | {
      forExecutor: ForExecutor;
    }
  | string;

export type TextPrompt = { type: 'text'; value: string };
export type ImagePrompt = { type: 'image'; value: string };

export type Prompt = string | [TextPrompt, ...ImagePrompt[]];

export type FreeTextParams = {
  prompt: Prompt;
  field?: string;
  context?: string;
  namespace?: string;
  messages: { role: string; content: Prompt }[];
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

export type TextData = {
  id: string;
  content: string;
};

export type ImageData = {
  id: string;
  url: string;
};

type ChunkStream<D = unknown> = (data: D) => void;

const services: ServiceHandlers = {
  buffered: {
    chatgpt: {
      content: (data) => {
        const content = data?.choices?.[0]?.message?.content;

        if (!content) {
          throw new Error(lng('modai.error.failed_request'));
        }

        const id = data.id;

        return {
          id,
          content,
        };
      },
      image: (data) => {
        let url = data?.data?.[0]?.url;

        if (!url) {
          url = data?.data?.[0]?.b64_json;

          if (!url) {
            throw new Error(lng('modai.error.failed_request'));
          }

          url = `data:image/png;base64,${url}`;
        }

        return {
          id: `chatgpt-${Date.now()}-${Math.round(Math.random() * 1000)}`,
          url,
        };
      },
    },
    claude: {
      content: (data) => {
        const content = data?.content?.[0]?.text;

        if (!content) {
          throw new Error(lng('modai.error.failed_request'));
        }

        const id = data.id;

        return {
          id,
          content,
        };
      },
    },
    gemini: {
      content: (data) => {
        const content = data?.candidates?.[0]?.content?.parts?.[0]?.text;

        if (!content) {
          throw new Error(lng('modai.error.failed_request'));
        }

        return {
          id: `gemini-${Date.now()}-${Math.round(Math.random() * 1000)}`,
          content,
        };
      },
      image: (data) => {
        const base64 = data?.predictions?.[0]?.bytesBase64Encoded;

        if (!base64) {
          throw new Error(lng('modai.error.failed_request'));
        }

        return {
          id: `gemini-${Date.now()}-${Math.round(Math.random() * 1000)}`,
          url: `data:image/png;base64,${base64}`,
        };
      },
    },
  },
  stream: {
    chatgpt: {
      content: (newData, currentData) => {
        const currentContent = currentData?.content ?? '';
        const content = newData.choices?.[0]?.delta?.content || '';

        return {
          ...currentData,
          id: newData.id,
          content: `${currentContent}${content}`,
        };
      },
    },
    claude: {
      content: (newData, currentData) => {
        const currentContent = currentData?.content ?? '';

        const content = newData.delta?.text || '';

        return {
          ...currentData,
          content: `${currentContent}${content}`,
        };
      },
    },
    gemini: {
      content: (newData, currentData) => {
        const currentContent = currentData?.content ?? '';

        const content = newData.candidates?.[0]?.content?.parts?.[0]?.text || '';

        return {
          ...currentData,
          content: `${currentContent}${content}`,
        };
      },
    },
  },
};

const errorHandler = async (res: Response) => {
  if (!res.ok) {
    const data = await res.json();
    if (data?.error) {
      throw new Error(data.error.message);
    }

    throw new Error(`${res.status} ${res.statusText}`);
  }
};

const handleStream = async (
  res: Response,
  service: string,
  parser: 'content',
  onChunkStream?: ChunkStream<TextData>,
  signal?: AbortSignal,
): Promise<TextData> => {
  if (!res.body) {
    throw new Error('failed');
  }

  const reader = res.body.getReader();
  const decoder = new TextDecoder('utf-8');
  let buffer = '';
  let currentData: TextData = {
    id: `${service}-${Date.now()}-${Math.round(Math.random() * 1000)}`,
    content: '',
  };

  while (true) {
    if (signal && signal.aborted) {
      break;
    }

    const { done, value } = await reader.read();
    if (done) break;

    const chunk = decoder.decode(value, { stream: true });

    if (service === 'gemini') {
      const jsonLines = chunk
        .trim()
        .split(',\r\n')
        .map((line) => line.replace(/^\[|]$/g, ''))
        .filter((line) => line.trim() !== '');
      for (const line of jsonLines) {
        try {
          const parsedData = JSON.parse(line);
          currentData = services.stream[service][parser](parsedData, currentData);
          if (onChunkStream) {
            onChunkStream(currentData);
          }
        } catch {
          /* empty */
        }
      }
    }

    if (service === 'chatgpt') {
      buffer += chunk;

      let lastNewlineIndex = 0;
      let newlineIndex;

      while ((newlineIndex = buffer.indexOf('\n', lastNewlineIndex)) !== -1) {
        const line = buffer.slice(lastNewlineIndex, newlineIndex).trim();
        lastNewlineIndex = newlineIndex + 1;

        if (line.startsWith('data: ')) {
          const data = line.slice(6);

          if (data === '[DONE]') {
            continue;
          }

          try {
            const parsedData = JSON.parse(data);
            currentData = services.stream[service][parser](parsedData, currentData);
            if (onChunkStream) {
              onChunkStream(currentData);
            }
          } catch {
            /* empty */
          }
        }
      }

      buffer = buffer.slice(lastNewlineIndex);
    }

    if (service === 'claude') {
      buffer += chunk;

      let lastNewlineIndex = 0;
      let newlineIndex;

      while ((newlineIndex = buffer.indexOf('\n', lastNewlineIndex)) !== -1) {
        const line = buffer.slice(lastNewlineIndex, newlineIndex).trim();
        lastNewlineIndex = newlineIndex + 1;

        if (line.startsWith('data: ')) {
          const data = line.slice(6);

          try {
            const parsedData = JSON.parse(data);
            if (parsedData.type === 'message_start') {
              currentData.id = parsedData.message.id;
              continue;
            }

            if (parsedData.type !== 'content_block_delta') {
              continue;
            }

            currentData = services.stream[service][parser](parsedData, currentData);
            if (onChunkStream) {
              onChunkStream(currentData);
            }
          } catch {
            /* empty */
          }
        }
      }

      buffer = buffer.slice(lastNewlineIndex);
    }
  }

  return currentData;
};

const serviceExecutor = async <D extends ServiceResponse>(
  details: ExecutorData,
  onChunkStream?: ChunkStream<D>,
  controller?: AbortController,
): Promise<D> => {
  if (typeof details !== 'object' || !details.forExecutor) {
    return details as unknown as D;
  }

  const executorDetails = details.forExecutor;

  controller = !controller ? new AbortController() : controller;
  const signal = controller.signal;

  const callService = async (details: ForExecutor) => {
    const res = await fetch(details.url, {
      signal,
      method: 'POST',
      body: details.body,
      headers: details.headers,
    });

    await errorHandler(res);

    const data = await res.json();

    if (data.error) {
      throw new Error(data.error.message);
    }

    return data;
  };

  const callStreamService = async (details: ForExecutor) => {
    if (executorDetails.parser !== 'content') {
      throw new Error(lng('modai.error.service_unsupported'));
    }

    const res = await fetch(details.url, {
      signal,
      method: 'POST',
      body: details.body,
      headers: details.headers,
    });

    await errorHandler(res);

    return handleStream(
      res,
      executorDetails.service,
      executorDetails.parser,
      onChunkStream as ChunkStream<TextData>,
    );
  };

  if (!executorDetails.service || !executorDetails.parser) {
    throw new Error(lng('modai.error.service_required'));
  }

  if (
    !services[executorDetails.stream ? 'stream' : ('buffered' as BufferMode)]?.[
      executorDetails.service as ServiceType
    ]?.[executorDetails.parser as keyof ServiceHandlers[BufferMode][ServiceType]]
  ) {
    throw new Error(lng('modai.error.service_unsupported'));
  }

  if (executorDetails.stream) {
    return callStreamService(executorDetails) as Promise<D>;
  }

  const data = await callService(executorDetails);
  return services['buffered'][executorDetails.service as ServiceType][
    executorDetails.parser as keyof ServiceHandlers['buffered'][ServiceType]
  ](data) as D;
};

const modxFetch = async <R>(action: string, params: Record<string, unknown>) => {
  const res = await fetch(`${globalState.config.apiURL}?action=${action}`, {
    method: 'POST',
    body: JSON.stringify(params),
    headers: {
      'Content-Type': 'application/json',
    },
  });

  if (!res.ok) {
    const data = await res.json();
    if (data.error) {
      throw new Error(data.error.message);
    }

    throw new Error(data.detail);
  }

  return (await res.json()) as R;
};

const aiFetch = async <D extends ServiceResponse>(
  action: string,
  params: Record<string, unknown>,
  onChunkStream?: ChunkStream<D>,
  controller?: AbortController,
): Promise<D> => {
  controller = !controller ? new AbortController() : controller;
  const signal = controller.signal;

  const res = await fetch(`${globalState.config.apiURL}?action=${action}`, {
    signal,
    method: 'POST',
    body: JSON.stringify(params),
    headers: {
      'Content-Type': 'application/json',
    },
  });

  if (!res.ok) {
    const data = await res.json();
    if (data.error) {
      throw new Error(data.error.message);
    }

    throw new Error(data.detail);
  }

  const service = res.headers.get('x-modai-service') ?? 'chatgpt';
  const parser = res.headers.get('x-modai-parser') ?? 'content';
  const stream = parseInt(res.headers.get('x-modai-stream') ?? '0') === 1;
  const proxy = parseInt(res.headers.get('x-modai-proxy') ?? '0') === 1;

  if (!proxy) {
    const data = await res.json();
    return serviceExecutor<D>(data, onChunkStream, controller);
  }

  if (!service || !parser) {
    controller.abort();
    throw new Error(lng('modai.error.service_required'));
  }

  if (
    !services[stream ? 'stream' : ('buffered' as BufferMode)]?.[service as ServiceType]?.[
      parser as keyof ServiceHandlers[BufferMode][ServiceType]
    ]
  ) {
    controller.abort();
    throw new Error(lng('modai.error.service_unsupported'));
  }

  if (!stream) {
    const data = await res.json();
    return services['buffered'][service as ServiceType][
      parser as keyof ServiceHandlers['buffered'][ServiceType]
    ](data) as D;
  }

  if (parser !== 'content') {
    throw new Error(lng('modai.error.service_unsupported'));
  }

  return (await handleStream(
    res,
    service,
    parser,
    onChunkStream as ChunkStream<TextData>,
    signal,
  )) as D;
};

export const executor = {
  mgr: {
    download: {
      image: async (params: DownloadImageParams) => {
        return await modxFetch<{ url: string; fullUrl: string }>('Download\\Image', params);
      },
    },
    prompt: {
      freeText: async (
        params: FreeTextParams,
        onChunkStream?: ChunkStream<TextData>,
        controller?: AbortController,
      ) => {
        return aiFetch('Prompt\\FreeText', params, onChunkStream, controller);
      },
      text: async (
        params: TextParams,
        onChunkStream?: ChunkStream<TextData>,
        controller?: AbortController,
      ) => {
        return aiFetch('Prompt\\Text', params, onChunkStream, controller);
      },
      vision: async (
        params: VisionParams,
        onChunkStream?: ChunkStream<TextData>,
        controller?: AbortController,
      ) => {
        return aiFetch('Prompt\\Vision', params, onChunkStream, controller);
      },
      image: async (params: ImageParams, controller?: AbortController) => {
        return aiFetch<ImageData>('Prompt\\Image', params, undefined, controller);
      },
    },
  },
};
