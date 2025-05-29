import { globalState } from '../../globalState';
import { button } from '../dom/button';
import { icon } from '../dom/icon';
import { check } from '../icons';
import { createElement } from '../utils';

import type { Modal } from './types';
import type { Message } from '../../chatHistory';

type ActionButtonConfig<M extends Message = Message> = {
  label: string;
  icon: string;
  message: M;
  loadingText?: string;
  completedText?: string;
  completedTextDuration?: number;
  onClick: (msg: M, modal: Modal) => void | Promise<void>;
  disabled?: boolean;
  disableCompletedState?: boolean;
};

const defaultConfig: Partial<ActionButtonConfig> = {
  loadingText: 'Loading...',
  completedText: 'Completed!',
  completedTextDuration: 2000,
  disabled: false,
  disableCompletedState: false,
};

export const createActionButton = <M extends Message>(config: ActionButtonConfig<M>) => {
  config = {
    ...defaultConfig,
    ...config,
  };

  const onClick = async () => {
    const result = config.onClick(config.message, globalState.modal);

    if (result instanceof Promise) {
      const spinner = createElement('span', 'spinner', [
        createElement('span', 'dot top'),
        createElement('span', 'dot right'),
        createElement('span', 'dot bottom'),
        createElement('span', 'dot left'),
      ]);

      iconEl.innerHTML = '';
      iconEl.appendChild(spinner);

      await result;
    }

    if (!config.disableCompletedState) {
      iconEl.innerHTML = check;
      await new Promise((resolve) => setTimeout(resolve, 2000));
    }

    iconEl.innerHTML = config.icon;

    btn.innerHTML = '';
    btn.append(iconEl, tooltip);
  };

  const iconEl = icon(14, config.icon);
  iconEl.ariaHidden = 'true';

  const tooltip = createElement('span', 'tooltip', config.label);

  const btn = button([iconEl, tooltip], onClick, 'action-button', {
    ariaLabel: config.label,
    tabIndex: 0,
  });

  if (config.disabled) {
    btn.disable();
  }

  return btn;
};
