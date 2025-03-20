import { button } from '../dom/button';
import { icon } from '../dom/icon';
import { chevronDown } from '../icons';
import { createElement } from '../utils';
import { scrollToBottom } from './modalActions';
import { globalState } from '../../globalState';
import { lng } from '../../lng';

export const buildScrollToBottom = () => {
  const scrollWrapper = createElement('div', 'scrollToBottomContainer', [
    button(
      [createElement('div', undefined, lng('modai.ui.scroll_to_bottom')), icon(14, chevronDown)],
      () => {
        scrollToBottom();
        checkScroll();
      },
    ),
  ]);

  globalState.modal.chatContainer.addEventListener('scroll', () => {
    checkScroll();
  });

  const observer = new MutationObserver(() => {
    checkScroll();
  });
  observer.observe(globalState.modal.chatContainer, { childList: true, subtree: true });

  globalState.modal.scrollWrapper = scrollWrapper;

  return scrollWrapper;
};

export const checkScroll = () => {
  const distanceFromBottom =
    globalState.modal.chatContainer.scrollHeight -
    globalState.modal.chatContainer.scrollTop -
    globalState.modal.chatContainer.clientHeight;
  const isAtBottom = distanceFromBottom < 10;

  if (
    globalState.modal.chatContainer.scrollHeight > globalState.modal.chatContainer.clientHeight &&
    !isAtBottom
  ) {
    globalState.modal.scrollWrapper.style.display = 'flex';
  } else {
    globalState.modal.scrollWrapper.style.display = 'none';
  }
};
