import { globalState } from '../../globalState';
import { button } from '../dom/button';
import { icon } from '../dom/icon';
import { textSelect } from '../icons';
import { applyStyles, createElement } from '../utils';

import type { UserMessageContext } from '../../chatHistory';

export type ContextWrapper = HTMLDivElement & {
  visible: boolean;
  show: () => void;
  hide: () => void;
  addContext: (context: UserMessageContext) => void;
  addContexts: (contexts: UserMessageContext[]) => void;
  removeContext: (context: Context) => void;
  removeContexts: () => void;
  contexts: Context[];
};

export type Context = UserMessageContext & {
  el?: HTMLElement;
};

const contextRenderers: Record<string, undefined | ((context: UserMessageContext) => HTMLElement)> =
  {
    selection: (ctx) => {
      const tooltipEl = createElement('span', 'tooltip', ctx.value, { tabIndex: -1 });
      const btn = button(
        [icon(24, textSelect), tooltipEl, createElement('div', 'trigger', '×', { tabIndex: -1 })],
        () => {
          globalState.modal.context.removeContext(ctx);
        },
        'context',
      );

      btn.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
          e.preventDefault();
          const scrollAmount = 30;
          tooltipEl.scrollTop += e.key === 'ArrowDown' ? scrollAmount : -scrollAmount;
        }
      });

      return btn;
    },
  };

export const buildModalInputContexts = () => {
  const contextWrapper = createElement('div', 'contextsWrapper') as ContextWrapper;
  contextWrapper.visible = false;
  contextWrapper.contexts = [];

  contextWrapper.show = () => {
    if (contextWrapper.visible) {
      return;
    }

    contextWrapper.visible = true;
    applyStyles(contextWrapper, 'contextsWrapper visible');
  };

  contextWrapper.hide = () => {
    if (!contextWrapper.visible) {
      return;
    }

    contextWrapper.visible = false;
    applyStyles(contextWrapper, 'contextsWrapper');
  };

  contextWrapper.removeContexts = () => {
    globalState.modal.context.contexts.forEach((ctx) => {
      globalState.modal.context.removeContext(ctx);
    });
  };

  contextWrapper.addContexts = (contexts) => {
    contexts.forEach((ctx) => {
      globalState.modal.context.addContext(ctx);
    });
  };

  contextWrapper.addContext = (context) => {
    const index = contextWrapper.contexts.push(context) - 1;

    const renderer = contextRenderers[context?.renderer || ''];
    if (renderer) {
      contextWrapper.show();
      const el = renderer(context);
      contextWrapper.contexts[index].el = el;
      contextWrapper.appendChild(el);
    }
  };

  contextWrapper.removeContext = (context) => {
    const index = contextWrapper.contexts.indexOf(context);
    if (index === -1) {
      return;
    }

    context.el?.remove();
    contextWrapper.contexts.splice(index, 1);

    if (
      contextWrapper.contexts.length === 0 ||
      contextWrapper.contexts.every((c) => c.el === undefined)
    ) {
      contextWrapper.hide();
    }
  };

  globalState.modal.context = contextWrapper;

  return contextWrapper;
};
