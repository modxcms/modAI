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

const formatBody = (details: ForExecutor) => {
  const isForm = details.contentType.toLowerCase() === 'multipart/form-data';
  if (!isForm) {
    return JSON.stringify(details.body);
  }

  const formData = new FormData();
  for (const [name, value] of Object.entries(details.body)) {
    const type = typeof value;

    if (value === null || type === 'undefined') {
      continue;
    }

    if (type === 'string' || type === 'number' || type === 'boolean') {
      formData.append(name, String(value));
      continue;
    }

    formData.append(name, JSON.stringify(value));
  }

  for (const [name, value] of Object.entries(details.binary)) {
    value.forEach((file, index) => {
      const binaryString = atob(file.base64);
      const len = binaryString.length;
      const bytes = new Uint8Array(len);
      for (let i = 0; i < len; i++) {
        bytes[i] = binaryString.charCodeAt(i);
      }

      const blob = new Blob([bytes], { type: file.mimeType });

      formData.append(name, blob, `source_${name}_${index}`);
    });
  }

  return formData;
};

const callService = async (details: ForExecutor, signal?: AbortSignal) => {
  const headers = details.headers;

  const isForm = details.contentType.toLowerCase() === 'multipart/form-data';
  if (!isForm) {
    headers['Content-Type'] = details.contentType;
  }

  const res = await fetch(details.url, {
    signal,
    method: 'POST',
    body: formatBody(details),
    headers,
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
  const streamHandler = getStreamHandler(
    executorDetails.service,
    executorDetails.parser,
    executorDetails.model,
  );

  const headers = executorDetails.headers;

  const isForm = executorDetails.contentType.toLowerCase() === 'multipart/form-data';
  if (!isForm) {
    headers['Content-Type'] = executorDetails.contentType;
  }

  const res = await fetch(executorDetails.url, {
    signal,
    method: 'POST',
    body: formatBody(executorDetails),
    headers,
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

  const serviceParser = getServiceParser(
    executorDetails.service,
    executorDetails.parser,
    executorDetails.model,
  );

  const data = await callService(executorDetails, signal);

  return serviceParser(data) as D;
};
