import { chatHistory } from './chatHistory';
import { executor } from './executor';
import { globalState } from './globalState';
import { history } from './history';
import { lng } from './lng';
import { initOnResource } from './resource';
import { ui } from './ui';

export type AvailableAgent = {
  id: string;
  name: string;
  contextProviders: string[] | null;
};

export type Config = {
  name?: string;
  assetsURL: string;
  apiURL: string;
  cssURL: string;
  translateFn?: (key: string, params?: Record<string, string>) => string;
  availableAgents: Record<string, AvailableAgent>;
};

export const init = (config: Config) => {
  globalState.config = config;

  return {
    chatHistory,
    history,
    executor,
    ui,
    lng,
    initOnResource,
  };
};
