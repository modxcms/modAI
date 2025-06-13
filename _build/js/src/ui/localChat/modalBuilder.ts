import { createElement, debounce } from '../utils';
import { scrollToBottom } from './modalActions';
import { buildModalChat } from './modalChat';
import { buildModalHeader } from './modalHeader';
import { buildModalInput } from './modalInput';
import { portal } from './portal';
import { buildResizer } from './resizer';
import { buildSidebar } from './sidebar';
import { loadModalState, saveModalState } from './state';
import { chatHistory } from '../../chatHistory';
import { globalState } from '../../globalState';
import { lng } from '../../lng';
import { createModAIShadow } from '../dom/modAIShadow';

import type { Modal, LocalChatConfig } from './types';
import type { UpdatableHTMLElement } from '../../chatHistory/types';

const debouncedSaveModalState = debounce(saveModalState, 300);

export const buildModal = (config: LocalChatConfig) => {
  const { shadow, shadowRoot } = createModAIShadow<Modal>(true, () => {
    scrollToBottom('instant');
    shadow.messageInput.focus();
  });

  const chatModal = createElement('div', 'modai--root chat-modal', '', {
    ariaLabel: lng('modai.ui.modai_assistant_chat_dialog'),
  });

  const modalState = loadModalState();
  if (modalState.position) {
    chatModal.style.width = modalState.position.width ?? '';
    chatModal.style.height = modalState.position.height ?? '';
    chatModal.style.top = modalState.position.top ?? '';
    chatModal.style.left = modalState.position.left ?? '';
    chatModal.style.transform = 'none';
  }

  const resizeObserver = new ResizeObserver(() => {
    debouncedSaveModalState();
    const msg = globalState.modal.chatMessages.lastElementChild as UpdatableHTMLElement | null;

    if (msg) {
      msg.syncHeight?.();
    }
  });

  resizeObserver.observe(chatModal);

  shadow.modal = chatModal;
  globalState.modal = shadow;
  globalState.modal.actionButtons = [];
  globalState.modal.modalButtons = [];

  if (config.persist) {
    chatModal.append(buildSidebar());
  }

  chatModal.append(buildModalHeader(config));
  chatModal.append(buildModalChat());
  chatModal.append(buildModalInput(config));

  const disclaimer = createElement('div', 'disclaimer', lng('modai.ui.disclaimer'));
  chatModal.append(disclaimer);

  chatModal.append(buildResizer());

  shadowRoot.appendChild(chatModal);
  shadowRoot.appendChild(portal);
  document.body.append(shadow);

  shadow.isDragging = false;
  shadow.isLoading = false;
  shadow.abortController = undefined;
  shadow.offsetX = 0;
  shadow.offsetY = 0;

  shadow.history = chatHistory.init({
    key: `${config.namespace ?? 'modai'}/${config.key}/${config.type}`,
    persist: config.persist,
  });

  return shadow;
};
