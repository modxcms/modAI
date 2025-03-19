import { globalState } from './globalState';

export type Lng = (key: string, params?: Record<string, string>) => string;

// Declare _ locally to avoid using it in other files
declare function _(key: string, params?: Record<string, string>): string;

export const lng: Lng = (key, params) => {
  return (globalState.config.translateFn || _)(key, params);
};
