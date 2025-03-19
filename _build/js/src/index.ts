import { chatHistory } from './chatHistory';
import { executor } from './executor';
import { globalState } from './globalState';
import { history } from './history';
import { initOnResource } from './resource';
import { ui } from './ui';

export type Config = {
  name?: string;
  apiURL: string;
  cssURL: string;
  translateFn?: (key: string, params?: Record<string, string>) => string;
};

export const init = (config: Config) => {
  globalState.config = config;

  return {
    chatHistory,
    history,
    executor,
    ui,
    initOnResource,
  };
};
