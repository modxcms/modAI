import { lng } from '../../lng';
import { anthropic } from './handlers/anthropic';
import { google } from './handlers/google';
import { legacyOpenai } from './handlers/legacyOpenai';
import { openai } from './handlers/openai';
import { openrouter } from './handlers/openrouter';

import type { StreamHandler, ChunkStream, TextData } from '../types';

const streamHandlers: Record<string, StreamHandler> = {
  openai,
  google,
  anthropic,
  openrouter,
  legacyOpenai,
};

const handleStream = async (
  res: Response,
  service: string,
  model: string,
  streamHandler: StreamHandler,
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
    usage: {
      completionTokens: 0,
      promptTokens: 0,
    },
  } as TextData;

  while (true) {
    if (signal?.aborted) {
      break;
    }

    const { done, value } = await reader.read();
    if (done) break;

    const chunk = decoder.decode(value, { stream: true });

    const result = streamHandler(chunk, buffer, currentData);
    if (onChunkStream && currentData?.content) {
      onChunkStream({
        ...currentData,
        metadata: {
          model,
        },
      });
    }

    buffer = result.buffer;
    currentData = result.currentData;
  }

  if (currentData.toolCalls) {
    currentData.toolCalls = currentData.toolCalls.filter(Boolean);
  }

  return {
    ...currentData,
    metadata: {
      model,
    },
  };
};

export const getStreamHandler = (
  service: string | undefined,
  parser: string | undefined,
  model: string | undefined,
) => {
  if (!service || !parser || !model) {
    throw new Error(lng('modai.error.service_required'));
  }

  if (parser !== 'content') {
    throw new Error(lng('modai.error.service_unsupported'));
  }

  if (!streamHandlers[service]) {
    throw new Error(lng('modai.error.service_unsupported'));
  }

  return (res: Response, onChunkStream?: ChunkStream<TextData>, signal?: AbortSignal) =>
    handleStream(res, service, model, streamHandlers[service], onChunkStream, signal);
};
