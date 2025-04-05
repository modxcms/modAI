import { lng } from '../../lng';
import { button } from '../dom/button';
import { createModAIShadow } from '../dom/modAIShadow';
import { createElement } from '../utils';

import type { Button } from '../dom/button';

type ConfirmDialogOptions = {
  title: string;
  content: string;
  onConfirm: () => void;
  onCancel?: () => void;
  confirmText: string;
  cancelText?: string;
  showConfirm?: boolean;
  showCancel?: boolean;
};

export const confirmDialog = (config: ConfirmDialogOptions) => {
  config = {
    showConfirm: true,
    showCancel: true,
    ...config,
  };

  const { shadow, shadowRoot } = createModAIShadow(false, () => {
    if (config.showConfirm) {
      confirmBtn.focus();
    }

    if (!config.showConfirm && config.showCancel) {
      cancelBtn.focus();
    }
  });

  const cancelBtn = button(
    config.cancelText ?? lng('modai.ui.cancel'),
    () => {
      closeDialog();
    },
    'cancelBtn',
    { tabIndex: -1 },
  );
  const confirmBtn = button(
    config.confirmText,
    () => {
      destroyDialog();
      config.onConfirm();
    },
    'confirmBtn',
    { tabIndex: -1 },
  );

  const overlay = createElement(
    'div',
    'modai--root overlay',
    [
      createElement(
        'div',
        'dialog',
        [
          createElement('h3', 'title', config.title),
          createElement('p', 'message', config.content),
          createElement('div', 'buttons', [
            config.showCancel && cancelBtn,
            config.showConfirm && confirmBtn,
          ]),
        ],
        { tabIndex: -1 },
      ),
    ],
    {
      ariaModal: 'true',
      role: 'dialog',
      ariaLabel: config.title,
    },
  );

  const destroyDialog = () => {
    document.removeEventListener('keydown', handleDialogKeyDown);
    overlay.remove();
    shadow.remove();
  };

  const closeDialog = () => {
    destroyDialog();
    config.onCancel?.();
  };

  const focusableElements: Button[] = [];
  if (config.showCancel) {
    focusableElements.push(cancelBtn);
  }

  if (config.showConfirm) {
    focusableElements.push(confirmBtn);
  }

  const handleDialogKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
      e.stopImmediatePropagation();
      e.preventDefault();
      e.stopPropagation();
      closeDialog();
    }

    if (e.key === 'Tab') {
      const focusedElement = shadowRoot.activeElement;
      let currentIndex = focusedElement ? focusableElements.indexOf(focusedElement as Button) : -1;

      if (e.shiftKey) {
        currentIndex = (currentIndex - 1 + focusableElements.length) % focusableElements.length;
      } else {
        currentIndex = (currentIndex + 1) % focusableElements.length;
      }

      if (focusableElements.length > 0) {
        focusableElements[currentIndex].focus();
      }

      e.preventDefault();
    }
  };

  overlay.addEventListener('keydown', handleDialogKeyDown);

  shadowRoot.appendChild(overlay);
  document.body.append(shadow);
};
