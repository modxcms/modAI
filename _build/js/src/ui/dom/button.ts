import { createElement } from '../utils';

export type Button = HTMLButtonElement & {
  enable: () => void;
  disable: () => void;
};

export const button = (
  content: string | HTMLElement | Element | (HTMLElement | Element | string)[],
  onClick: (e: MouseEvent) => Promise<void> | void,
  styleObj?: string,
  btnProps?: Partial<HTMLButtonElement>,
) => {
  const textContent = typeof content === 'string' ? content : '';

  if (typeof content !== 'string' && !Array.isArray(content)) {
    content = [content];
  }

  const el = createElement('button', styleObj, textContent) as Button;
  el.type = 'button';

  if (Array.isArray(content)) {
    content.forEach((i) => {
      if (typeof i === 'string') {
        el.append(document.createTextNode(i));
      } else {
        el.append(i);
      }
    });
  }

  el.addEventListener('click', onClick);

  el.enable = () => {
    el.disabled = false;
  };

  el.disable = () => {
    el.disabled = true;
  };

  if (btnProps) {
    Object.assign(el, btnProps);
  }

  return el;
};
