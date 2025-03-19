import { globalState } from '../../globalState';

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
