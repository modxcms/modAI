import { chatHistory } from './chatHistory';
import { executor } from './executor';
import { addHandler } from './executor/services';
import { addStreamHandler } from './executor/streamHandlers';
import { globalState } from './globalState';
import { history } from './history';
import { lng } from './lng';
import { mgr } from './mgr';
import { checkPermissions } from './permissions';
import { ui } from './ui';

import type { Permissions } from './permissions';
import type { NestedSelectData } from './ui/dom/nestedSelect';

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
  promptLibrary: { text?: NestedSelectData; image?: NestedSelectData };
  permissions: Record<Permissions, 1 | 0>;
  chatAdditionalControls: Record<
    string,
    {
      name: string;
      label: string;
      icon?: string;
      values: Record<string, string>;
    }[]
  >;
};

export const init = (config: Config) => {
  globalState.config = config;

  return {
    chatHistory,
    history,
    executor,
    ui,
    lng,
    mgr,
    checkPermissions,
  };
};

export const registerService = (
  name: string,
  handler: Parameters<typeof addHandler>[1],
  streamHandler: Parameters<typeof addStreamHandler>[1],
) => {
  addHandler(name, handler);
  addStreamHandler(name, streamHandler);
};
