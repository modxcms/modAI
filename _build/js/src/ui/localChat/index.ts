import { closeModal, sendMessage } from './modalActions';
import { buildModal } from './modalBuilder';
import { globalState } from '../../globalState';
import { lng } from '../../lng';

import type { LocalChatConfig } from './types';

export const modalTypes = ['text', 'image'] as const;

export const createModal = (config: LocalChatConfig) => {
  if (globalState.modalOpen) {
    return;
  }

  if (!config.key) {
    alert(lng('modai.error.key_required'));
    return;
  }

  if (!config.type) {
    config.type = 'text';
  }

  if (!config.availableTypes) {
    config.availableTypes = [config.type];
  }

  config.availableTypes = config.availableTypes.filter((type) => modalTypes.includes(type));

  if (config.availableTypes.length > 0 && !config.availableTypes.includes(config.type)) {
    config.availableTypes.unshift(config.type);
  }

  const modal = buildModal(config);

  modal.api = {
    sendMessage: async (providedMessage?: string, hidePrompt?: boolean) => {
      await sendMessage(config, providedMessage, hidePrompt);
    },
    closeModal: () => {
      closeModal();
    },
  };

  globalState.modalOpen = true;

  return modal;
};
