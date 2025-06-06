import { globalState } from '../../globalState';
import { createElement } from '../utils';

export const buildResizer = () => {
  let isResizing = false;
  let rafId: null | number = null;
  let pendingResize: null | { clientX: number; clientY: number } = null;
  let initialState: null | {
    width: number;
    height: number;
    x: number;
    y: number;
    mouseX: number;
    mouseY: number;
  } = null;

  const resizeHandle = createElement('div', 'resize-handle');

  resizeHandle.addEventListener('pointerdown', startResize);

  function startResize(e: PointerEvent) {
    if (isResizing || e.button !== 0) {
      return;
    }

    isResizing = true;

    const rect = globalState.modal.modal.getBoundingClientRect();
    initialState = {
      width: rect.width,
      height: rect.height,
      x: rect.left,
      y: rect.top,
      mouseX: e.clientX,
      mouseY: e.clientY,
    };

    document.body.style.cursor = 'nw-resize';

    try {
      resizeHandle.setPointerCapture(e.pointerId);
    } catch {
      isResizing = false;
      document.body.style.cursor = '';
      return;
    }
    resizeHandle.addEventListener('pointermove', handleResize, { passive: false });
    resizeHandle.addEventListener('pointerup', stopResize);

    e.preventDefault();
    e.stopPropagation();
  }

  function handleResize(e: PointerEvent) {
    if (!isResizing || !initialState) {
      return;
    }

    e.preventDefault();

    pendingResize = {
      clientX: e.clientX,
      clientY: e.clientY,
    };

    if (!rafId) {
      rafId = requestAnimationFrame(applyResize);
    }
  }

  function applyResize() {
    if (!pendingResize || !isResizing || !initialState) {
      rafId = null;
      return;
    }

    const deltaX = pendingResize.clientX - initialState.mouseX;
    const deltaY = pendingResize.clientY - initialState.mouseY;

    let newWidth = initialState.width + deltaX;
    let newHeight = initialState.height + deltaY;

    // also set in modai.css
    const minWidth = 468;
    const minHeight = 500;
    const maxWidth = window.innerWidth - initialState.x;
    const maxHeight = window.innerHeight - initialState.y;

    newWidth = Math.max(minWidth, Math.min(maxWidth, newWidth));
    newHeight = Math.max(minHeight, Math.min(maxHeight, newHeight));

    globalState.modal.modal.style.width = newWidth + 'px';
    globalState.modal.modal.style.height = newHeight + 'px';

    rafId = null;
    pendingResize = null;
  }

  function stopResize(e: PointerEvent) {
    if (!isResizing) {
      return;
    }

    e.preventDefault();
    e.stopPropagation();

    isResizing = false;
    document.body.style.cursor = '';

    if (rafId) {
      cancelAnimationFrame(rafId);
      rafId = null;
    }

    if (initialState) {
      pendingResize = { clientX: e.clientX, clientY: e.clientY };
      applyResize();
    }

    initialState = null;

    try {
      resizeHandle.releasePointerCapture(e.pointerId);
    } catch {
      // not needed
    }

    resizeHandle.removeEventListener('pointermove', handleResize);
    resizeHandle.removeEventListener('pointerup', stopResize);
  }

  return resizeHandle;
};
