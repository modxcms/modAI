import { services } from './services';

import type { TextData } from './services';
import type { ChunkStream } from './types';

export type StreamHandler = (
  chunk: string,
  buffer: string,
  currentData: TextData,
  onChunkStream?: ChunkStream<TextData>,
) => { buffer: string; currentData: TextData };

export const streamHandlers: Record<string, StreamHandler> = {
  gemini: (chunk, buffer, currentData, onChunkStream) => {
    const jsonLines = chunk
      .trim()
      .split(',\r\n')
      .map((line) => line.replace(/^\[|]$/g, ''))
      .filter((line) => line.trim() !== '');

    for (const line of jsonLines) {
      try {
        const parsedData = JSON.parse(line);
        currentData = services.stream.gemini.content(parsedData, currentData);

        if (onChunkStream && currentData.content) {
          onChunkStream(currentData);
        }
      } catch {
        /* empty */
      }
    }

    return { buffer, currentData };
  },

  chatgpt: (chunk, buffer, currentData, onChunkStream) => {
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
          currentData = services.stream.chatgpt.content(parsedData, currentData);

          if (onChunkStream && currentData?.content) {
            onChunkStream(currentData);
          }
        } catch {
          /* empty */
        }
      }
    }

    return { buffer: buffer.slice(lastNewlineIndex), currentData };
  },

  claude: (chunk, buffer, currentData, onChunkStream) => {
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
            currentData.usage = {
              promptTokens: parsedData.message.usage.input_tokens,
              completionTokens: 0,
            };
            continue;
          }

          if (parsedData.type === 'message_delta') {
            currentData.usage.completionTokens = parsedData.usage.output_tokens;
            continue;
          }

          if (
            parsedData.type !== 'content_block_delta' &&
            parsedData.type !== 'content_block_start'
          ) {
            continue;
          }

          currentData = services.stream.claude.content(parsedData, currentData as TextData);
          if (onChunkStream && currentData.content) {
            onChunkStream(currentData);
          }
        } catch {
          /* empty */
        }
      }
    }

    return { buffer: buffer.slice(lastNewlineIndex), currentData };
  },
};

export const handleStream = async (
  res: Response,
  service: string,
  onChunkStream?: ChunkStream<TextData>,
  signal?: AbortSignal,
): Promise<TextData> => {
  if (!res.body) {
    throw new Error('Response body is empty');
  }

  const reader = res.body.getReader();
  const decoder = new TextDecoder('utf-8');
  let buffer = '';
  let currentData = {
    id: `${service}-${Date.now()}-${Math.round(Math.random() * 1000)}`,
  } as TextData;

  const streamHandler = streamHandlers[service];
  if (!streamHandler) {
    throw new Error('Unsupported stream handler');
  }

  while (true) {
    if (signal?.aborted) {
      break;
    }

    const { done, value } = await reader.read();
    if (done) break;

    const chunk = decoder.decode(value, { stream: true });

    const result = streamHandler(chunk, buffer, currentData, onChunkStream);
    buffer = result.buffer;
    currentData = result.currentData;
  }

  if (currentData.toolCalls) {
    currentData.toolCalls = currentData.toolCalls.filter(Boolean);
  }

  return currentData;
};
