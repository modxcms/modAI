import { globalState } from '../../globalState';

import type { ModalState } from './types';
import type { Button } from '../dom/button';

export const setLoadingState = (loading: boolean) => {
  globalState.modal.isLoading = loading;

  if (loading) {
    globalState.modal.loadingIndicator.style.display = 'flex';
  } else {
    globalState.modal.loadingIndicator.style.display = 'none';
  }

  globalState.modal.messageInput.disabled = loading;

  if (loading) {
    globalState.modal.sendBtn.disable();
    globalState.modal.stopBtn.enable();

    globalState.modal.closeModalBtn.disable();
  } else {
    globalState.modal.sendBtn.disable();
    globalState.modal.stopBtn.disable();

    globalState.modal.closeModalBtn.enable();
  }

  globalState.modal.modeButtons.forEach((btn) => {
    if (loading) {
      btn.disable();
    } else {
      btn.enable();
    }
  });

  const hasMessages = globalState.modal.history.getMessages().length > 0;
  globalState.modal.controlButtons.forEach((btn) => {
    if (loading) {
      btn.disable();
    } else {
      btn.enable();
    }
  });

  globalState.modal.actionButtons.forEach((btn) => {
    if (loading || !hasMessages) {
      btn.disable();
    } else {
      btn.enable();
    }
  });

  const actionButtons = globalState.modal.chatMessages.querySelectorAll(
    '.action-button',
  ) as NodeListOf<Button>;
  actionButtons.forEach((button) => {
    if (loading) {
      button.disable?.();
    } else {
      button?.enable?.();
    }
  });
};
// @ts-expect-error asd
window.setLoadingState = setLoadingState;
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
