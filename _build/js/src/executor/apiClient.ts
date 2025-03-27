import { globalState } from '../globalState';
import { lng } from '../lng';
import { serviceExecutor } from './serviceExecutor';
import { services, validateServiceParser } from './services';
import { handleStream } from './streamHandlers';

import type { TextData } from './services';
import type { ChunkStream, ServiceResponse } from './types';

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
      throw new Error(data.error.message);
    }

    throw new Error(data.detail);
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

  if (!proxy) {
    const data = await res.json();
    return serviceExecutor<D>(data, onChunkStream, controller);
  }

  const service = res.headers.get('x-modai-service') ?? 'chatgpt';
  const parser = res.headers.get('x-modai-parser') ?? 'content';

  try {
    const {
      service: validService,
      parser: validParser,
      mode,
    } = validateServiceParser(service, parser, stream);

    if (mode === 'stream') {
      if (validParser !== 'content') {
        throw new Error(lng('modai.error.service_unsupported'));
      }

      return (await handleStream(
        res,
        validService,
        onChunkStream as ChunkStream<TextData>,
        signal,
      )) as D;
    }

    const data = await res.json();

    return services.buffered[validService][validParser](data) as D;
  } catch (error) {
    controller.abort();
    throw error;
  }
};
