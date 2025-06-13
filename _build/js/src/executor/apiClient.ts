import { globalState } from '../globalState';
import { ApiError } from './apiError';
import { serviceExecutor } from './serviceExecutor';
import { getServiceParser } from './services';
import { getStreamHandler } from './streamHandlers';

import type { TextData, ChunkStream, ServiceResponse } from './types';

export const modxFetch = async <R>(
  action: string,
  params: Record<string, unknown>,
  controller?: AbortController,
) => {
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
      throw new ApiError(data.error.message, res.status);
    }

    throw new ApiError(data.detail, res.status);
  }

  return (await res.json()) as R;
};

export const aiFetch = async <D extends ServiceResponse>(
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

  const stream = parseInt(res.headers.get('x-modai-stream') ?? '0') === 1;
  const proxy = parseInt(res.headers.get('x-modai-proxy') ?? '0') === 1;

  let chatId = res.headers.has('x-modai-chat-id')
    ? Number(res.headers.get('x-modai-chat-id'))
    : undefined;
  chatId = Number.isInteger(chatId) ? chatId : undefined;

  if (!proxy) {
    const data = await res.json();
    const parsedData = await serviceExecutor<D>(data, onChunkStream, controller);

    parsedData.chatId = chatId;

    return parsedData;
  }

  const service = res.headers.get('x-modai-service') ?? undefined;
  const parser = res.headers.get('x-modai-parser') ?? undefined;
  const model = res.headers.get('x-modai-model') ?? undefined;

  try {
    if (stream) {
      const streamHandler = getStreamHandler(service, parser, model);

      const data = (await streamHandler(res, onChunkStream as ChunkStream<TextData>, signal)) as D;
      data.chatId = chatId;

      return data;
    }

    const serviceParser = getServiceParser(service, parser, model);

    const data = await res.json();

    const parsedData = serviceParser(data) as D;
    parsedData.chatId = chatId;

    return parsedData;
  } catch (error) {
    controller.abort();
    throw error;
  }
};
