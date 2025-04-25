export const applyStyles = (element: HTMLElement, styleObj: string) => {
  element.className = styleObj;
};

type El = string | HTMLElement | Element | false | undefined;

export const createElement = <K extends keyof HTMLElementTagNameMap>(
  type: K,
  styleObj?: string,
  content?: El | El[],
  elProps?: Partial<HTMLElementTagNameMap[K]>,
): HTMLElementTagNameMap[K] => {
  const textContent = typeof content === 'string' ? content : '';

  if (content && typeof content !== 'string' && !Array.isArray(content)) {
    content = [content];
  }

  const element = document.createElement(type);
  if (elProps) {
    Object.assign(element, elProps);
  }

  if (styleObj) {
    element.className = styleObj;
  }

  if (textContent) {
    element.textContent = textContent;
  } else if (content) {
    if (Array.isArray(content)) {
      for (const item of content) {
        if (!item) {
          continue;
        }

        if (typeof item === 'string') {
          element.append(document.createTextNode(item));
          continue;
        }

        element.append(item);
      }
    }
  }

  return element;
};

export const nlToBr = (content: string) => {
  return /<[^>]*>/g.test(content) ? content : content.replace(/\r\n|\n|\r/g, '<br>');
};

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function debounce<T extends (...args: any[]) => void>(func: T, delay: number) {
  let timeoutId: number | undefined;
  return function (this: ThisParameterType<T>, ...args: Parameters<T>) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
      func.apply(this, args);
    }, delay);
  };
}
