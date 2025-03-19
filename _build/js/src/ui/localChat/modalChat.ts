import { globalState } from '../../globalState';
import { createElement } from '../utils';

export const buildModalChat = () => {
  const chatContainer = createElement('div', 'chatContainer', '', {
    ariaLive: 'polite',
  });

  const welcome = createElement('div', 'welcome', [
    createElement(
      'p',
      'greeting',
      globalState.config.name ? `Welcome, ${globalState.config.name}.` : 'Welcome!',
    ),
    createElement('p', 'msg', 'How can I help you today? ✨'),
  ]);

  chatContainer.append(welcome);

  const chatHistory = createElement('div', 'history', '', {
    ariaLabel: 'Conversation history',
  });
  chatContainer.append(chatHistory);

  globalState.modal.welcomeMessage = welcome;
  globalState.modal.chatMessages = chatHistory;
  globalState.modal.chatContainer = chatContainer;

  return chatContainer;
};
