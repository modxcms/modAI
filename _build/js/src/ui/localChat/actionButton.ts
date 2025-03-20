import { globalState } from '../../globalState';
import { lng } from '../../lng';
import { button } from '../dom/button';
import { check } from '../icons';
import { createElement } from '../utils';

import type { Modal } from './types';
import type { Message } from '../../chatHistory';

type ActionButtonConfig = {
  label: string;
  icon: Element;
  message: Message;
  loadingText?: string;
  completedText?: string;
  completedTextDuration?: number;
  onClick: (msg: Message, modal: Modal) => void | Promise<void>;
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

export const createActionButton = (config: ActionButtonConfig) => {
  config = {
    ...defaultConfig,
    ...config,
  };

  const onClick = async () => {
    const originalHTML = btn.innerHTML;
    const result = config.onClick(config.message, globalState.modal);

    if (result instanceof Promise) {
      const spinner = createElement('span', 'spinner', [
        createElement('span', 'dot top'),
        createElement('span', 'dot right'),
        createElement('span', 'dot bottom'),
        createElement('span', 'dot left'),
      ]);

      icon.innerHTML = '';
      icon.appendChild(spinner);
      label.innerHTML = config.loadingText || '';

      await result;
    }

    if (!config.disableCompletedState) {
      icon.innerHTML = check;
      label.innerHTML = config.completedText || lng('modai.ui.completed');

      await new Promise((resolve) => setTimeout(resolve, 2000));
    }

    btn.innerHTML = originalHTML;
  };

  const icon = config.icon;
  icon.ariaHidden = 'true';

  const label = createElement('div', undefined, config.label);

  const btn = button([icon, label], onClick, 'action-button', {
    ariaLabel: config.label,
    tabIndex: 0,
  });

  if (config.disabled) {
    btn.disable();
  }

  return btn;
};
