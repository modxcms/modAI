import { createElement, debounce } from '../utils';
import { renderMessage } from './messageHandlers';
import { scrollToBottom } from './modalActions';
import { buildModalChat } from './modalChat';
import { buildModalHeader } from './modalHeader';
import { buildModalInput } from './modalInput';
import { loadModalState, saveModalState } from './state';
import { chatHistory } from '../../chatHistory';
import { globalState } from '../../globalState';
import { lng } from '../../lng';
import { createModAIShadow } from '../dom/modAIShadow';

import type { Modal, LocalChatConfig } from './types';
import type { UpdatableHTMLElement } from '../../chatHistory';

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

  chatModal.append(buildModalHeader());
  chatModal.append(buildModalChat());
  chatModal.append(buildModalInput(config));

  const disclaimer = createElement('div', 'disclaimer', lng('modai.ui.disclaimer'));
  chatModal.append(disclaimer);

  shadowRoot.appendChild(chatModal);
  document.body.append(shadow);

  shadow.isDragging = false;
  shadow.isLoading = false;
  shadow.abortController = undefined;
  shadow.offsetX = 0;
  shadow.offsetY = 0;

  shadow.history = chatHistory.init({
    key: `${config.namespace ?? 'modai'}/${config.key}/${config.type}`,
    onAddMessage: (msg) => renderMessage(msg, config) as UpdatableHTMLElement | undefined,
    persist: config.persist,
    onInitDone: () => {
      const messages = shadow.history.getMessages().filter((m) => !m.hidden);
      if (messages.length > 0) {
        shadow.welcomeMessage.style.display = 'none';

        shadow.actionButtons.forEach((btn) => {
          btn.enable();
        });
      }
    },
  });

  const messages = shadow.history.getMessages().filter((m) => !m.hidden);
  if (messages.length > 0) {
    shadow.welcomeMessage.style.display = 'none';
    shadow.actionButtons.forEach((btn) => {
      btn.enable();
    });

    messages.forEach((msg) => {
      if (msg.el) {
        msg.el.classList.remove('new');
        shadow.chatMessages.appendChild(msg.el);
      }
    });
  }

  return shadow;
};
