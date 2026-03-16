import { globalState } from '../../globalState';

const clamp = (value: number, min: number, max: number) => Math.max(min, Math.min(max, value));

const getMaxCoordinates = (modal: HTMLElement) => ({
  maxX: Math.max(0, window.innerWidth - modal.offsetWidth),
  maxY: Math.max(0, window.innerHeight - modal.offsetHeight),
});

export const clampModalToViewport = (modal: HTMLElement) => {
  const currentRect = modal.getBoundingClientRect();
  const currentX = Number.parseFloat(modal.style.left) || currentRect.left;
  const currentY = Number.parseFloat(modal.style.top) || currentRect.top;
  const { maxX, maxY } = getMaxCoordinates(modal);

  modal.style.left = `${clamp(currentX, 0, maxX)}px`;
  modal.style.top = `${clamp(currentY, 0, maxY)}px`;
  modal.style.transform = 'none';
};

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
  const { maxX, maxY } = getMaxCoordinates(modal);

  modal.style.left = `${clamp(newX, 0, maxX)}px`;
  modal.style.top = `${clamp(newY, 0, maxY)}px`;
  modal.style.transform = 'none';
};

export const endDrag = () => {
  globalState.modal.isDragging = false;
  document.body.style.userSelect = '';
};
