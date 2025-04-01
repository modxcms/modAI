import { services, validateServiceParser } from './services';
import { handleStream, validStreamingService } from './streamHandlers';

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

  if (executorDetails.stream) {
    validStreamingService(executorDetails.service, executorDetails.parser);

    return (await callStreamService(executorDetails)) as D;
  }

  const { service, parser } = validateServiceParser(
    executorDetails.service,
    executorDetails.parser,
  );

  const data = await callService(executorDetails);

  return services[service][parser](data) as D;
};
