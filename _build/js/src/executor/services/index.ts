import { anthropic } from './handlers/anthropic';
import { google } from './handlers/google';
import { legacyOpenai } from './handlers/legacyOpenai';
import { openai } from './handlers/openai';
import { lng } from '../../lng';
import { openrouter } from './handlers/openrouter';

import type { ServiceHandler } from '../types';

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const services: Record<string, ServiceHandler<any, any>> = {
  openai,
  google,
  anthropic,
  openrouter,
  legacyOpenai,
};

export const addHandler = <CData, IData>(name: string, handler: ServiceHandler<CData, IData>) => {
  if (services[name]) {
    return;
  }

  services[name] = handler;
};

export const getServiceParser = (
  service: string | undefined,
  parser: string | undefined,
  model: string | undefined,
) => {
  if (!service || !parser || !model) {
    throw new Error(lng('modai.error.service_required'));
  }

  const serviceType = service as keyof typeof services;
  const parserType = parser as keyof ServiceHandler<unknown, unknown>;

  const serviceParser = services[serviceType]?.[parserType];

  if (!serviceParser) {
    throw new Error(lng('modai.error.service_unsupported'));
  }

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  return (data: any) => {
    return {
      ...serviceParser(data),
      metadata: {
        model,
      },
    };
  };
};
