import { anthropic } from './handlers/anthropic';
import { google } from './handlers/google';
import { openai } from './handlers/openai';
import { lng } from '../../lng';
import { openrouter } from './handlers/openrouter';

import type { ServiceHandler } from '../types';

const services = {
  openai,
  google,
  anthropic,
  openrouter,
};

export const getServiceParser = (service: string | undefined, parser: string | undefined) => {
  if (!service || !parser) {
    throw new Error(lng('modai.error.service_required'));
  }

  const serviceType = service as keyof typeof services;
  const parserType = parser as keyof ServiceHandler<unknown, unknown>;

  if (!services[serviceType]?.[parserType]) {
    throw new Error(lng('modai.error.service_unsupported'));
  }

  return services[serviceType]?.[parserType];
};
