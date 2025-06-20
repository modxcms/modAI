import { closeModal } from './modalActions';
import { button } from '../dom/button';
import { createElement } from '../utils';
import { drag, endDrag, initDrag } from './dragHandlers';
import { globalState } from '../../globalState';
import { lng } from '../../lng';
import { icon } from '../dom/icon';
import { expand, minimize, x } from '../icons';
import { saveModalState } from './state';

const centerModal = (element: HTMLElement) => {
  const modalWidth = element.offsetWidth;
  const modalHeight = element.offsetHeight;

  const viewportWidth = window.innerWidth;
  const viewportHeight = window.innerHeight;

  const newLeft = viewportWidth / 2 - modalWidth / 2;
  const newTop = viewportHeight / 2 - modalHeight / 2;

  element.style.left = `${newLeft}px`;
  element.style.top = `${newTop}px`;
};

export const buildModalHeader = () => {
  const closeModalBtn = button(
    icon(24, x),
    () => {
      closeModal();
    },
    '',
    { ariaLabel: lng('modai.ui.close_dialog') },
  );

  const isMaximized = globalState.modal.modal.style.width === '90%';

  const buttonsWrapper = createElement('div', 'buttonsWrapper', [
    button(
      icon(24, isMaximized ? minimize : expand),
      (e) => {
        const self = e.currentTarget as HTMLButtonElement;

        if (globalState.modal.modal.style.width === '90%') {
          globalState.modal.modal.style.width = '';
          globalState.modal.modal.style.height = '';
          globalState.modal.modal.style.transform = 'none';

          saveModalState();

          self.ariaLabel = lng('modai.ui.maximize_dialog');
          self.innerHTML = '';
          self.appendChild(icon(24, expand));

          centerModal(globalState.modal.modal);
          return;
        }

        globalState.modal.modal.style.width = '90%';
        globalState.modal.modal.style.height = '90%';
        globalState.modal.modal.style.transform = 'none';

        saveModalState();

        self.ariaLabel = lng('modai.ui.minimize_dialog');
        self.innerHTML = '';
        self.appendChild(icon(24, minimize));

        centerModal(globalState.modal.modal);
      },
      '',
      {
        ariaLabel: isMaximized ? lng('modai.ui.minimize_dialog') : lng('modai.ui.maximize_dialog'),
      },
    ),
    closeModalBtn,
  ]);

  const header = createElement('header', 'header', [
    createElement('h1', '', lng('modai.ui.modai_assistant')),
    buttonsWrapper,
  ]);

  header.addEventListener('mousedown', (e) => {
    initDrag(e);
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', () => {
      endDrag();
      document.removeEventListener('mousemove', drag);
      document.removeEventListener('mouseup', endDrag);
      saveModalState();
    });
  });

  const onKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
      e.preventDefault();
      closeModal();
      document.removeEventListener('keydown', onKeyDown);
    }
  };

  document.addEventListener('keydown', onKeyDown);

  globalState.modal.closeModalBtn = closeModalBtn;

  return header;
};
