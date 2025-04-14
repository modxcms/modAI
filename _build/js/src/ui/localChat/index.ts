import { closeModal, sendMessage } from './modalActions';
import { buildModal } from './modalBuilder';
import { globalState } from '../../globalState';
import { lng } from '../../lng';
import { checkPermissions } from '../../permissions';

import type { LocalChatConfig } from './types';

export const modalTypes = ['text', 'image'] as const;

export const createModal = (config: LocalChatConfig) => {
  if (globalState.modalOpen) {
    return;
  }

  if (config.context) {
    if (!config.withContexts) {
      config.withContexts = [];
    }

    config.withContexts.push({
      __type: 'selection',
      name: 'Selection',
      renderer: 'selection',
      value: config.context,
    });

    config.context = undefined;
  }

  if (!config.key) {
    alert(lng('modai.error.key_required'));
    return;
  }

  /**
   * @deprecated
   */
  if (!config.type) {
    config.type = 'text';
  }

  if (!config.type) {
    alert(lng('modai.error.type_required'));
    return;
  }

  const hasPermissions = verifyPermissions(config);
  if (!hasPermissions) {
    return;
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

  if (config.withContexts) {
    globalState.modal.context.addContexts(config.withContexts);
  }

  globalState.modalOpen = true;

  return modal;
};

export const verifyPermissions = (config: LocalChatConfig) => {
  if (!checkPermissions(['modai_client'])) {
    return false;
  }

  if (
    !checkPermissions(['modai_client_chat_image']) &&
    !checkPermissions(['modai_client_chat_text'])
  ) {
    return false;
  }

  if (!config.availableTypes) {
    config.availableTypes = [config.type];
  }

  config.availableTypes = config.availableTypes
    .filter((type) => modalTypes.includes(type))
    .filter((type) =>
      checkPermissions([type === 'text' ? 'modai_client_chat_text' : 'modai_client_chat_image']),
    );

  if (config.availableTypes.length > 0 && !config.availableTypes.includes(config.type)) {
    config.type = config.availableTypes[0];
  }

  if (config.availableTypes.length === 0 || !config.availableTypes.includes(config.type)) {
    return false;
  }

  return true;
};
