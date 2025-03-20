import { globalState } from '../../globalState';
import { lng } from '../../lng';
import { createElement } from '../utils';

export const buildModalChat = () => {
  const chatContainer = createElement('div', 'chatContainer', '', {
    ariaLive: 'polite',
  });

  const welcome = createElement('div', 'welcome', [
    createElement(
      'p',
      'greeting',
      globalState.config.name
        ? lng('modai.ui.greeting_with_name', { name: globalState.config.name })
        : lng('modai.ui.greeting'),
    ),
    createElement('p', 'msg', lng('modai.ui.welcome_msg')),
  ]);

  chatContainer.append(welcome);

  const chatHistory = createElement('div', 'history', '', {
    ariaLabel: lng('modai.ui.conversation_history'),
  });
  chatContainer.append(chatHistory);

  globalState.modal.welcomeMessage = welcome;
  globalState.modal.chatMessages = chatHistory;
  globalState.modal.chatContainer = chatContainer;

  return chatContainer;
};
