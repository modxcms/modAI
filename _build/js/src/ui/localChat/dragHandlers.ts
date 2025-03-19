import { globalState } from '../../globalState';

export const initDrag = (e: MouseEvent) => {
  globalState.modal.isDragging = true;

  const modal = globalState.modal.modal;
  const rect = modal.getBoundingClientRect();

  globalState.modal.offsetX = e.clientX - rect.left;
  globalState.modal.offsetY = e.clientY - rect.top;

  document.body.style.userSelect = 'none';
};

export const drag = (e: MouseEvent) => {
  if (!globalState.modal.isDragging) return;

  const modal = globalState.modal.modal;
  const newX = e.clientX - globalState.modal.offsetX;
  const newY = e.clientY - globalState.modal.offsetY;

  modal.style.left = newX + 'px';
  modal.style.top = newY + 'px';
  modal.style.transform = 'none';
};

export const endDrag = () => {
  globalState.modal.isDragging = false;
  document.body.style.userSelect = '';
};
