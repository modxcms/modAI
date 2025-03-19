import { createElement } from '../utils';
import { renderMessage } from './messageHandlers';
import { scrollToBottom } from './modalActions';
import { buildModalChat } from './modalChat';
import { buildModalHeader } from './modalHeader';
import { buildModalInput } from './modalInput';
import { chatHistory } from '../../chatHistory';
import { globalState } from '../../globalState';
import { createModAIShadow } from '../dom/modAIShadow';

import type { Modal, LocalChatConfig } from './types';

export const buildModal = (config: LocalChatConfig) => {
  const { shadow, shadowRoot } = createModAIShadow<Modal>(true, () => {
    scrollToBottom();
    shadow.messageInput.focus();
  });

  const chatModal = createElement('div', 'modai--root chat-modal', '', {
    ariaLabel: 'modAI Assistant chat dialog',
  });

  shadow.modal = chatModal;
  globalState.modal = shadow;

  shadow.history = chatHistory.init(`${config.key}/${config.type}`, (msg) => {
    return renderMessage(msg, config);
  });

  chatModal.append(buildModalHeader());
  chatModal.append(buildModalChat());
  chatModal.append(buildModalInput(config));

  const disclaimer = createElement(
    'div',
    'disclaimer',
    'AI can make mistakes. Please double-check responses.',
  );
  chatModal.append(disclaimer);

  shadowRoot.appendChild(chatModal);
  document.body.append(shadow);

  shadow.isDragging = false;
  shadow.isLoading = false;
  shadow.abortController = undefined;
  shadow.offsetX = 0;
  shadow.offsetY = 0;

  const messages = shadow.history.getMessages().filter((m) => !m.hidden);
  if (messages.length > 0) {
    shadow.welcomeMessage.style.display = 'none';
    shadow.actionButtons.forEach((btn) => {
      btn.enable();
    });

    messages.forEach((msg) => {
      if (msg.el) {
        shadow.chatMessages.appendChild(msg.el);
      }
    });
  }

  return shadow;
};
