import { chatHistory } from './chatHistory';
import { executor } from './executor';
import { initGlobalButton } from './globalButton';
import { globalState } from './globalState';
import { history } from './history';
import { lng } from './lng';
import { checkPermissions } from './permissions';
import { initOnResource } from './resource';
import { ui } from './ui';

import type { Permissions } from './permissions';

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
  permissions: Record<Permissions, 1 | 0>;
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
    checkPermissions,
    initGlobalButton,
  };
};
