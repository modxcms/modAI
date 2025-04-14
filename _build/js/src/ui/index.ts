import { createGenerateButton } from './generateButton';
import { createModal, verifyPermissions } from './localChat';
import { LocalChatConfig } from './localChat/types';
import { createLoadingOverlay } from './overlay';

type LocalChat = {
  /**
   * @deprecated use the ui.localChat.createModal instead
   */
  (config: LocalChatConfig): ReturnType<typeof createModal>;
  createModal: typeof createModal;
  verifyPermissions: typeof verifyPermissions;
};

export const ui = {
  createLoadingOverlay,
  localChat: Object.assign({}, createModal, {
    createModal: createModal,
    verifyPermissions,
  }) as LocalChat,
  generateButton: createGenerateButton,
};
