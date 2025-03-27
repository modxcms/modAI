import { lng } from '../lng';
import { services, validateServiceParser } from './services';
import { handleStream } from './streamHandlers';

import type { TextData } from './services';
import type { ChunkStream, ExecutorData, ForExecutor, ServiceResponse } from './types';

const errorHandler = async (res: Response) => {
  if (!res.ok) {
    const data = await res.json();
    if (data?.error) {
      throw new Error(data.error.message);
    }

    throw new Error(`${res.status} ${res.statusText}`);
  }
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
      onChunkStream as ChunkStream<TextData>,
      signal,
    );
  };

  const { service, parser, mode } = validateServiceParser(
    executorDetails.service,
    executorDetails.parser,
    executorDetails.stream,
  );

  if (mode === 'stream') {
    return (await callStreamService(executorDetails)) as D;
  }

  const data = await callService(executorDetails);

  return services[mode][service][parser](data) as D;
};
