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
  let activeHandle: HTMLElement | null = null;
  let fromLeft = false;
  let fromTop = false;

  const resizeHandleWrapper = createElement('div');
  const resizeHandles = [
    createElement('div', 'resize-handle bottom-right'),
    createElement('div', 'resize-handle bottom-left'),
    createElement('div', 'resize-handle top-right'),
    createElement('div', 'resize-handle top-left'),
  ];

  resizeHandleWrapper.append(...resizeHandles);

  resizeHandles.forEach((resizeHandle) => {
    resizeHandle.addEventListener('pointerdown', startResize);
  });

  function startResize(e: PointerEvent) {
    if (isResizing || e.button !== 0) {
      return;
    }

    isResizing = true;
    activeHandle = e.currentTarget as HTMLElement;

    const rect = globalState.modal.modal.getBoundingClientRect();
    initialState = {
      width: rect.width,
      height: rect.height,
      x: rect.left,
      y: rect.top,
      mouseX: e.clientX,
      mouseY: e.clientY,
    };

    // Determine which edges are being resized based on the handle classes
    const cls = (activeHandle?.className || '').toString();
    fromLeft = cls.includes('left');
    fromTop = cls.includes('top');

    // Set appropriate diagonal cursor
    let cursor: string;
    if (fromLeft === fromTop) {
      cursor = 'nwse-resize';
    } else {
      cursor = 'nesw-resize';
    }
    document.body.style.cursor = cursor;

    try {
      activeHandle.setPointerCapture(e.pointerId);
    } catch {
      // If pointer capture fails, still proceed with document listeners
    }

    document.addEventListener('pointermove', handleResize, { passive: false });
    document.addEventListener('pointerup', stopResize);

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

    // also set in modai.css
    const minWidth = 468;
    const minHeight = 500;

    const right = initialState.x + initialState.width;
    const bottom = initialState.y + initialState.height;

    // Helper clamp
    const clamp = (val: number, min: number, max: number) => Math.max(min, Math.min(max, val));

    let newLeft = initialState.x;
    let newTop = initialState.y;
    let newWidth: number;
    let newHeight: number;

    if (fromLeft) {
      // Move left edge; right edge stays fixed
      newLeft = clamp(initialState.x + deltaX, 0, right - minWidth);
      newWidth = right - newLeft;
    } else {
      // Move right edge; left edge stays fixed
      const maxWidth = window.innerWidth - initialState.x;
      newWidth = clamp(initialState.width + deltaX, minWidth, maxWidth);
    }

    if (fromTop) {
      // Move top edge; bottom edge stays fixed
      newTop = clamp(initialState.y + deltaY, 0, bottom - minHeight);
      newHeight = bottom - newTop;
    } else {
      // Move bottom edge; top edge stays fixed
      const maxHeight = window.innerHeight - initialState.y;
      newHeight = clamp(initialState.height + deltaY, minHeight, maxHeight);
    }

    const modal = globalState.modal.modal;
    if (fromLeft) modal.style.left = newLeft + 'px';
    if (fromTop) modal.style.top = newTop + 'px';
    modal.style.width = newWidth + 'px';
    modal.style.height = newHeight + 'px';

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
      activeHandle?.releasePointerCapture(e.pointerId);
    } catch {
      // not needed
    }
    document.removeEventListener('pointermove', handleResize);
    document.removeEventListener('pointerup', stopResize);
    activeHandle = null;
    fromLeft = false;
    fromTop = false;
  }

  return resizeHandleWrapper;
};
