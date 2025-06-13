import { createElement } from '../utils';

export const tooltip = (text: string) => {
  return createElement('span', 'tooltip', text);
};
