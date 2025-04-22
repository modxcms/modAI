import { globalState } from '../../globalState';
import { createElement } from '../utils';

export const createModAIShadow = <R extends HTMLElement>(
  inline?: boolean,
  onLoad?: () => void,
): {
  shadow: R;
  shadowRoot: ShadowRoot;
} => {
  const shadow = createElement('div') as unknown as R;
  const shadowRoot = shadow.attachShadow({ mode: 'open' });

  shadow.style.display = 'none';

  const link = createElement('link', undefined, '', {
    rel: 'stylesheet',
    type: 'text/css',
    href: globalState.config.cssURL,
  });

  shadowRoot.appendChild(link);

  link.onload = () => {
    if (inline) {
      shadow.style.display = 'inline-block';
    } else {
      shadow.style.display = '';
    }

    if (onLoad) {
      setTimeout(() => {
        onLoad();
      }, 1);
    }
  };

  return { shadow, shadowRoot };
};
