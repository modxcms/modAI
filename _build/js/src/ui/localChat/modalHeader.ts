import { closeModal } from './modalActions';
import { button } from '../dom/button';
import { createElement } from '../utils';
import { drag, endDrag, initDrag } from './dragHandlers';
import { globalState } from '../../globalState';
import { lng } from '../../lng';
import { icon } from '../dom/icon';
import { x } from '../icons';

export const buildModalHeader = () => {
  const closeModalBtn = button(
    icon(24, x),
    () => {
      closeModal();
    },
    'closeBtn',
    { ariaLabel: lng('modai.ui.close_dialog') },
  );

  const header = createElement('header', 'header', [
    createElement('h1', '', lng('modai.ui.modai_assistant')),
    closeModalBtn,
  ]);

  header.addEventListener('mousedown', (e) => {
    initDrag(e);
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', () => {
      endDrag();
      document.removeEventListener('mousemove', drag);
      document.removeEventListener('mouseup', endDrag);
    });
  });

  document.addEventListener('keydown', (e) => {
    if (globalState.alertOpen) {
      return;
    }

    if (e.key === 'Escape') {
      e.preventDefault();
      closeModal();
    }
  });

  globalState.modal.closeModalBtn = closeModalBtn;

  return header;
};
