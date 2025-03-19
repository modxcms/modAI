import { executor, TextData, TextParams } from '../../executor';
import { DataOutput, history } from '../../history';
import { confirmDialog } from '../cofirmDialog';
import { button } from '../dom/button';
import { icon } from '../dom/icon';
import { createModAIShadow } from '../dom/modAIShadow';
import { arrowLeft, arrowRight, sparkle } from '../icons';
import { ui } from '../index';
import { LocalChatConfig } from '../localChat/types';
import { createLoadingOverlay } from '../overlay';
import { createElement } from '../utils';

type HistoryButton = HTMLButtonElement & {
  enable: () => void;
  disable: () => void;
};
type HistoryInfo = HTMLDivElement & {
  update: (showing: number, total: number) => void;
};
type HistoryNav = HTMLDivElement & {
  show: () => void;
  hide: () => void;
  prevButton: HistoryButton;
  nextButton: HistoryButton;
  info: HistoryInfo;
};
type HistoryElement = HTMLElement & { historyNav: HistoryNav };
type DataContext = {
  els: {
    onFieldChange: (data: DataOutput<DataContext>, noStore?: boolean) => void;
    wrapper: HistoryElement;
  }[];
};

const createWandEl = <R extends HTMLElement>(onClick: () => void | Promise<void>) => {
  const { shadow, shadowRoot } = createModAIShadow<R>(true);

  const generate = createElement(
    'div',
    'modai--root generate',
    button(icon(14, sparkle), onClick, 'btn', {
      type: 'button',
      title: 'Generate using AI',
    }),
  );

  shadowRoot.appendChild(generate);

  return { shadow, shadowRoot, generate };
};

const createHistoryNav = (cache: ReturnType<typeof history.init<DataContext>>) => {
  const prevButton = button(
    icon(14, arrowLeft),
    () => {
      cache.prev();
    },
    'history--prev',
    {
      type: 'button',
      title: 'Previous Version',
      role: 'navigation',
    },
  );

  const nextButton = button(
    icon(14, arrowRight),
    () => {
      cache.next();
    },
    'history--next',
    {
      type: 'button',
      title: 'Next Version',
      role: 'navigation',
    },
  );

  const info = createElement('div') as HistoryInfo;
  info.update = (showing, total) => {
    info.innerText = `${showing}/${total}`;
  };

  const wrapper = createElement('div', 'history--wrapper') as HistoryNav;
  wrapper.show = () => {
    wrapper.style.display = 'inline-flex';
  };

  wrapper.hide = () => {
    wrapper.style.display = 'none';
  };

  wrapper.prevButton = prevButton;
  wrapper.nextButton = nextButton;
  wrapper.info = info;

  wrapper.appendChild(prevButton);
  wrapper.appendChild(nextButton);
  wrapper.appendChild(info);

  wrapper.hide();
  prevButton.disable();
  nextButton.disable();

  return wrapper;
};

type Target = {
  targetEl: HTMLElement;
};

const createLocalChat = (config: LocalChatConfig & Target) => {
  const { shadow } = createWandEl(() => {
    ui.localChat(config);
  });

  config.targetEl.appendChild(shadow);

  return shadow;
};

type ForcedTextConfig = {
  field: string;
  input: HTMLElement;
  onChange: (data: DataOutput<DataContext>, noStore?: boolean) => void;
  initialValue?: string;
} & TextParams &
  Target;
const createForcedTextPrompt = ({
  targetEl,
  input,
  onChange,
  initialValue,
  field,
  ...rest
}: ForcedTextConfig) => {
  const { shadow, generate } = createWandEl<HistoryElement>(async () => {
    const done = createLoadingOverlay(input);

    try {
      const result = await executor.mgr.prompt.text(
        {
          field,
          ...rest,
        },
        (data) => {
          cache.insert(data.content, true);
        },
      );
      cache.insert(result.content);
      done();
    } catch (err) {
      done();
      confirmDialog({
        title: 'Failed',
        content: _('modai.cmp.failed_try_again', { msg: err instanceof Error ? err.message : '' }),
        confirmText: 'Close',
        showCancel: false,
        onConfirm: () => {},
      });
    }
  });

  const cache = history.init<DataContext>(
    field,
    (data, noStore) => {
      data.context.els.forEach(({ wrapper, onFieldChange }) => {
        onFieldChange(data, noStore);

        if (data.total > 0) {
          wrapper.historyNav.show();
        }

        wrapper.historyNav.info.update(data.current, data.total);

        const root = wrapper.shadowRoot || wrapper.ownerDocument;
        const focusNext = !data.prevStatus && root.activeElement === wrapper.historyNav.prevButton;
        const focusPrev = !data.nextStatus && root.activeElement === wrapper.historyNav.nextButton;

        if (data.prevStatus) {
          wrapper.historyNav.prevButton.enable();
        } else {
          wrapper.historyNav.prevButton.disable();
        }

        if (data.nextStatus) {
          wrapper.historyNav.nextButton.enable();
        } else {
          wrapper.historyNav.nextButton.disable();
        }

        if (focusNext) {
          wrapper.historyNav.nextButton.focus();
        }

        if (focusPrev) {
          wrapper.historyNav.prevButton.focus();
        }
      });
    },
    initialValue,
    {} as DataContext,
  );

  if (!cache.cachedItem.context.els) {
    cache.cachedItem.context.els = [];
  }
  cache.cachedItem.context.els.push({ onFieldChange: onChange, wrapper: shadow });

  const historyNav = createHistoryNav(cache);

  generate.appendChild(historyNav);
  shadow.historyNav = historyNav;

  targetEl.appendChild(shadow);

  return shadow;
};

type VisionConfig = {
  image: HTMLImageElement;
  input: HTMLElement;
  field: string;
  onUpdate: (data: TextData) => void;
  namespace?: string;
};

const createVisionPrompt = (config: VisionConfig & Target) => {
  const { shadow } = createWandEl(async () => {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    canvas.width = config.image.width;
    canvas.height = config.image.height;

    ctx.drawImage(config.image, 0, 0);

    const base64Data = canvas.toDataURL('image/png');

    const done = createLoadingOverlay(config.input);

    try {
      const result = await executor.mgr.prompt.vision(
        {
          image: base64Data,
          field: config.field,
          namespace: config.namespace,
        },
        (data) => {
          config.onUpdate(data);
        },
      );

      config.onUpdate(result);
      done();
    } catch (err) {
      done();
      confirmDialog({
        title: 'Failed',
        content: _('modai.cmp.failed_try_again', { msg: err instanceof Error ? err.message : '' }),
        confirmText: 'Close',
        showCancel: false,
        onConfirm: () => {},
      });
    }
  });

  config.targetEl.appendChild(shadow);

  return shadow;
};

export const createGenerateButton = {
  localChat: createLocalChat,
  forcedText: createForcedTextPrompt,
  vision: createVisionPrompt,
};
