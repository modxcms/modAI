import { getServiceParser } from './services';
import { getStreamHandler } from './streamHandlers';

import type { TextData, ChunkStream, ExecutorData, ForExecutor, ServiceResponse } from './types';

const errorHandler = async (res: Response) => {
  if (!res.ok) {
    const data = await res.json();
    if (data?.error) {
      throw new Error(data.error.message);
    }

    throw new Error(`${res.status} ${res.statusText}`);
  }
};

const callService = async (details: ForExecutor, signal?: AbortSignal) => {
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

const callStreamService = async <D extends ServiceResponse>(
  executorDetails: ForExecutor,
  onChunkStream?: ChunkStream<D>,
  signal?: AbortSignal,
) => {
  const streamHandler = getStreamHandler(executorDetails.service, executorDetails.parser);

  const res = await fetch(executorDetails.url, {
    signal,
    method: 'POST',
    body: executorDetails.body,
    headers: executorDetails.headers,
  });

  await errorHandler(res);

  return streamHandler(res, onChunkStream as ChunkStream<TextData>, signal);
};

export const serviceExecutor = async <D extends ServiceResponse>(
  details: ExecutorData,
  onChunkStream?: ChunkStream<D>,
  controller?: AbortController,
): Promise<D> => {
  controller = !controller ? new AbortController() : controller;
  const signal = controller.signal;

  if (typeof details === 'string') {
    return details as unknown as D;
  }

  const { forExecutor: executorDetails } = details;

  if (executorDetails.stream) {
    return (await callStreamService(executorDetails, onChunkStream, signal)) as D;
  }

  const serviceParser = getServiceParser(executorDetails.service, executorDetails.parser);

  const data = await callService(executorDetails, signal);

  return serviceParser(data) as D;
};
