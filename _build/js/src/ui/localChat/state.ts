import { emitter } from './emitter';
import { globalState } from '../../globalState';

import type { ModalState } from './types';
import type { Button } from '../dom/button';

export const setLoadingState = (loading: boolean) => {
  globalState.modal.isLoading = loading;
  const hasMessages = globalState.modal.history.getMessages().length > 0;

  emitter.emit('loading', { isLoading: loading, isPreloading: false, hasMessages });
};

export const setPreloadingState = (loading: boolean) => {
  globalState.modal.isLoading = loading;
  const hasMessages = globalState.modal.history.getMessages().length > 0;

  emitter.emit('loading', { isLoading: loading, isPreloading: true, hasMessages });
};

emitter.on('loading', ({ eventData }) => {
  const actionButtons = globalState.modal.chatMessages.querySelectorAll(
    '.action-button',
  ) as NodeListOf<Button>;
  actionButtons.forEach((button) => {
    if (eventData.isLoading) {
      button.disable?.();
    } else {
      button?.enable?.();
    }
  });
});

const MODAL_STORAGE_KEY = 'modai__state';

export const saveModalState = () => {
  const currentState = loadModalState();
  currentState.position = {
    width: globalState.modal.modal.style.width,
    height: globalState.modal.modal.style.height,
    left: globalState.modal.modal.style.left,
    top: globalState.modal.modal.style.top,
  };

  try {
    localStorage.setItem(MODAL_STORAGE_KEY, JSON.stringify(currentState));
  } catch {
    /* not needed */
  }
};

export const loadModalState = (): ModalState => {
  try {
    const savedStateString = localStorage.getItem(MODAL_STORAGE_KEY);
    if (savedStateString) {
      return JSON.parse(savedStateString);
    }

    return {};
  } catch {
    return {};
  }
};
