import { portal } from './index';
import { icon } from '../../dom/icon';
import { createElement } from '../../utils';

const DROPDOWN_POSITION_OFFSET = 120;
const KEYBOARD_KEYS = {
  ENTER: 'Enter',
  SPACE: ' ',
  ESCAPE: 'Escape',
  TAB: 'Tab',
  ARROW_DOWN: 'ArrowDown',
  ARROW_UP: 'ArrowUp',
  HOME: 'Home',
  END: 'End',
} as const;

export interface DropdownItem {
  icon: string;
  text: string;
  onClick: () => void;
}

interface DropdownState {
  currentButton: HTMLElement | null;
  currentWrapper: HTMLElement | null;
  currentBodyElement: HTMLElement | null;
  clickHandler: ((e: Event) => void) | null;
  keyboardHandler: ((e: KeyboardEvent) => void) | null;
  focusTrapHandler: ((e: FocusEvent) => void) | null;
}

const dropdownState: DropdownState = {
  currentButton: null,
  currentWrapper: null,
  currentBodyElement: null,
  clickHandler: null,
  keyboardHandler: null,
  focusTrapHandler: null,
};

const updateDropdownItemFocus = (
  dropdownItems: HTMLElement[],
  currentIndex: number,
  newIndex: number,
) => {
  if (currentIndex >= 0 && currentIndex < dropdownItems.length) {
    dropdownItems[currentIndex].setAttribute('tabindex', '-1');
    dropdownItems[currentIndex].classList.remove('focused');
  }

  if (newIndex >= 0 && newIndex < dropdownItems.length) {
    dropdownItems[newIndex].setAttribute('tabindex', '0');
    dropdownItems[newIndex].classList.add('focused');
    dropdownItems[newIndex].focus();
  }
};

const positionDropdown = (dropdown: HTMLElement, button: HTMLElement, modalRect: DOMRect) => {
  const buttonRect = button.getBoundingClientRect();
  const portalRect = portal.getBoundingClientRect();

  const left = buttonRect.left - portalRect.left;
  const top = buttonRect.bottom - modalRect.top + DROPDOWN_POSITION_OFFSET;

  dropdown.style.left = `${left}px`;
  dropdown.style.top = `${top}px`;
};

export const showPortalDropdown = (
  button: HTMLElement,
  items: DropdownItem[],
  wrapperElement?: HTMLElement,
  sidebarElement?: HTMLElement,
  modalRect?: DOMRect,
) => {
  if (dropdownState.currentButton === button && portal.children.length > 0) {
    hidePortalDropdown();
    button.blur();
    return;
  }

  hidePortalDropdown();

  dropdownState.currentBodyElement = sidebarElement || null;
  if (dropdownState.currentBodyElement) {
    dropdownState.currentBodyElement.style.pointerEvents = 'none';
  }

  dropdownState.currentButton = button;
  dropdownState.currentWrapper = wrapperElement || null;
  button.setAttribute('aria-expanded', 'true');
  if (dropdownState.currentWrapper) {
    dropdownState.currentWrapper.classList.add('active');
  }

  button.style.pointerEvents = 'auto';
  button.style.zIndex = '10000';

  const dropdown = createElement('div', 'portal-dropdown', undefined, {
    role: 'menu',
  });
  dropdown.setAttribute('aria-labelledby', button.id || 'dropdown-button');

  const dropdownItems: HTMLElement[] = [];
  items.forEach((item) => {
    const dropdownItem = createElement(
      'div',
      'portal-dropdown-item',
      [icon(16, item.icon), createElement('span', undefined, item.text)],
      {
        role: 'menuitem',
        tabIndex: -1,
        ariaLabel: item.text,
      },
    );

    const handleItemAction = () => {
      item.onClick();
      hidePortalDropdown();
    };

    dropdownItem.addEventListener('click', handleItemAction);

    dropdownItem.addEventListener('keydown', (e) => {
      if (e.key === KEYBOARD_KEYS.ENTER || e.key === KEYBOARD_KEYS.SPACE) {
        e.preventDefault();
        handleItemAction();
      }
    });

    dropdownItem.addEventListener('mouseenter', () => {
      dropdownItems.forEach((item) => {
        item.classList.remove('focused');
        item.setAttribute('tabindex', '-1');
      });

      dropdownItem.classList.add('focused');
      dropdownItem.setAttribute('tabindex', '0');
      dropdownItem.focus();
    });

    dropdown.appendChild(dropdownItem);
    dropdownItems.push(dropdownItem);
  });

  portal.appendChild(dropdown);

  if (modalRect) {
    positionDropdown(dropdown, button, modalRect);
  }

  dropdown.classList.add('show');

  if (dropdownItems.length > 0) {
    dropdownItems[0].setAttribute('tabindex', '0');
    dropdownItems[0].classList.add('focused');
    requestAnimationFrame(() => {
      if (dropdownState.currentButton) {
        dropdownState.currentButton.blur();
      }
      dropdownItems[0].focus();
    });
  }

  const handleKeyDown = (e: KeyboardEvent) => {
    if (!dropdownState.currentButton || dropdownState.currentButton !== button) {
      return;
    }

    const focusedIndex = dropdownItems.findIndex((item) => item.classList.contains('focused'));

    switch (e.key) {
      case KEYBOARD_KEYS.ESCAPE:
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        hidePortalDropdown(true, button);
        break;
      case KEYBOARD_KEYS.TAB:
        e.preventDefault();
        if (e.shiftKey) {
          if (focusedIndex > 0) {
            updateDropdownItemFocus(dropdownItems, focusedIndex, focusedIndex - 1);
          } else {
            const lastIndex = dropdownItems.length - 1;
            updateDropdownItemFocus(dropdownItems, focusedIndex, lastIndex);
          }
        } else {
          if (focusedIndex < dropdownItems.length - 1) {
            const nextIndex = focusedIndex >= 0 ? focusedIndex + 1 : 0;
            updateDropdownItemFocus(dropdownItems, focusedIndex, nextIndex);
          } else {
            updateDropdownItemFocus(dropdownItems, focusedIndex, 0);
          }
        }
        break;
      case KEYBOARD_KEYS.ARROW_DOWN:
        e.preventDefault();
        if (focusedIndex < dropdownItems.length - 1) {
          const nextIndex = focusedIndex >= 0 ? focusedIndex + 1 : 0;
          updateDropdownItemFocus(dropdownItems, focusedIndex, nextIndex);
        }
        break;
      case KEYBOARD_KEYS.ARROW_UP:
        e.preventDefault();
        if (focusedIndex > 0) {
          updateDropdownItemFocus(dropdownItems, focusedIndex, focusedIndex - 1);
        }
        break;
      case KEYBOARD_KEYS.HOME:
        e.preventDefault();
        if (focusedIndex !== 0) {
          updateDropdownItemFocus(dropdownItems, focusedIndex, 0);
        }
        break;
      case KEYBOARD_KEYS.END: {
        e.preventDefault();
        const lastIndex = dropdownItems.length - 1;
        if (focusedIndex !== lastIndex) {
          updateDropdownItemFocus(dropdownItems, focusedIndex, lastIndex);
        }
        break;
      }
    }
  };

  const handleClickOutside = (e: Event) => {
    const composedPath = e.composedPath();

    const clickedInDropdown = composedPath.some(
      (element) => element instanceof Element && dropdown.contains(element),
    );

    if (clickedInDropdown) {
      return;
    }

    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    hidePortalDropdown();
  };

  const handleFocusTrap = (e: FocusEvent) => {
    if (!dropdownState.currentButton || dropdownState.currentButton !== button) {
      return;
    }

    const target = e.target as Element;

    if (dropdown.contains(target)) {
      return;
    }

    e.preventDefault();
    e.stopPropagation();

    const focusedItem = dropdownItems.find((item) => item.classList.contains('focused'));
    if (focusedItem) {
      focusedItem.focus();
      return;
    }

    if (dropdownItems.length > 0) {
      dropdownItems[0].classList.add('focused');
      dropdownItems[0].setAttribute('tabindex', '0');
      dropdownItems[0].focus();
    }
  };

  dropdownState.clickHandler = handleClickOutside;
  dropdownState.keyboardHandler = handleKeyDown;
  dropdownState.focusTrapHandler = handleFocusTrap;

  document.addEventListener('keydown', handleKeyDown, true);
  document.addEventListener('focusin', handleFocusTrap, true);
  document.addEventListener('click', handleClickOutside, true);
  portal.addEventListener('click', handleClickOutside, true);
};

export const hidePortalDropdown = (restoreFocus = false, buttonToFocus?: HTMLElement) => {
  if (dropdownState.currentBodyElement) {
    dropdownState.currentBodyElement.style.pointerEvents = '';
  }

  if (dropdownState.clickHandler) {
    document.removeEventListener('click', dropdownState.clickHandler, true);
    portal.removeEventListener('click', dropdownState.clickHandler, true);
    dropdownState.clickHandler = null;
  }

  if (dropdownState.keyboardHandler) {
    document.removeEventListener('keydown', dropdownState.keyboardHandler, true);
    dropdownState.keyboardHandler = null;
  }

  if (dropdownState.focusTrapHandler) {
    document.removeEventListener('focusin', dropdownState.focusTrapHandler, true);
    dropdownState.focusTrapHandler = null;
  }

  portal.innerHTML = '';

  if (dropdownState.currentButton) {
    dropdownState.currentButton.setAttribute('aria-expanded', 'false');
    dropdownState.currentButton.style.pointerEvents = '';
    dropdownState.currentButton.style.zIndex = '';

    if (!restoreFocus) {
      dropdownState.currentButton.blur();
    }
  }

  const wrapperToCleanup = dropdownState.currentWrapper;

  dropdownState.currentButton = null;
  dropdownState.currentWrapper = null;
  dropdownState.currentBodyElement = null;
  dropdownState.keyboardHandler = null;
  dropdownState.focusTrapHandler = null;

  if (restoreFocus && buttonToFocus) {
    if (wrapperToCleanup) {
      const actionsElement = wrapperToCleanup.querySelector('.actions') as HTMLElement;
      if (actionsElement) {
        actionsElement.style.transition = 'none';
      }
    }

    buttonToFocus.focus();

    if (wrapperToCleanup) {
      requestAnimationFrame(() => {
        wrapperToCleanup.classList.remove('active');
        const actionsElement = wrapperToCleanup.querySelector('.actions') as HTMLElement;
        if (actionsElement) {
          void actionsElement.offsetHeight;
          actionsElement.style.transition = '';
        }
      });
    }

    return;
  }

  if (wrapperToCleanup) {
    wrapperToCleanup.classList.remove('active');
  }
};
