import { createElement } from '../utils';
import { icon } from './icon';
import { bot, chevronRight } from '../icons';
import { Select } from './select';

export type NestedSelectItem =
  | {
      name: string;
      id: string;
      value: string;
      children?: undefined;
    }
  | {
      name: string;
      id: string;
      children: NestedSelectItem[];
    };
export type NestedSelectData = NestedSelectItem[];

export function buildNestedSelect(
  menuData: NestedSelectData,
  initialValue: undefined | string,
  onSelect: (selectedItem: NestedSelectItem) => void,
  options: {
    selectText?: string;
    tooltip?: string;
    icon?: string;
    showSelectedValue?: boolean;
    highlightSelectedValue?: boolean;
  },
) {
  const itemById = new Map<string, NestedSelectItem>();
  const parentById = new Map<string, NestedSelectItem>();
  const elementById = new Map<string, HTMLElement>();

  // State
  let isOpen = false;
  let currentFocus = -1;
  let focusableItems: HTMLElement[] = [];
  let selectedItem: NestedSelectItem | null = null;

  const button = createElement(
    'button',
    'dropdown-button',
    [
      options.icon && icon(24, options.icon),
      options.selectText && createElement('span', undefined, options.selectText),
      options.tooltip && createElement('span', 'tooltip', options.tooltip),
    ],
    {
      ariaHasPopup: 'true',
      ariaExpanded: 'false',
    },
  );

  button.addEventListener('click', toggleDropdown);
  button.addEventListener('keydown', handleButtonKeydown);

  const menu = createElement('div', 'dropdown-menu', generateMenuHtml(menuData), { role: 'menu' });
  const dropdownContainer = createElement('div', 'nestedSelectContainer', [button, menu]) as Select;

  dropdownContainer.enable = () => {
    button.disabled = false;
  };

  dropdownContainer.disable = () => {
    button.disabled = true;
  };

  // Initialize
  function generateMenuHtml(
    menuItems: NestedSelectData,
    parent?: NestedSelectItem,
    depth = 0,
  ): HTMLElement[] {
    return menuItems.map((menuItem) => {
      if (menuItem.children && menuItem.children.length > 0) {
        const submenu = createElement(
          'div',
          'submenu',
          generateMenuHtml(menuItem.children, menuItem, depth + 1),
          {
            role: 'menu',
          },
        );

        submenu.style.zIndex = String(1001 + depth);

        const dropdownItem = createElement(
          'div',
          'dropdown-item has-submenu',
          [
            createElement('div', 'submenuTrigger', [
              createElement('span', undefined, menuItem.name),
              icon(16, chevronRight),
            ]),
            submenu,
          ],
          {
            tabIndex: -1,
            ariaHasPopup: 'true',
            ariaExpanded: 'false',
          },
        );

        dropdownItem.addEventListener('click', (e) => {
          e.stopPropagation();
          e.stopImmediatePropagation();
          e.preventDefault();
        });
        dropdownItem.addEventListener('keydown', (e) => handleItemKeydown(e, menuItem));

        itemById.set(menuItem.id, menuItem);
        elementById.set(menuItem.id, dropdownItem);

        if (parent) {
          parentById.set(menuItem.id, parent);
        }

        return dropdownItem;
      }

      const dropdownItem = createElement('div', 'dropdown-item', menuItem.name, {
        role: 'menuitem',
        tabIndex: -1,
      });

      dropdownItem.addEventListener('click', () => {
        selectItem(menuItem);
      });
      dropdownItem.addEventListener('keydown', (e) => handleItemKeydown(e, menuItem));

      itemById.set(menuItem.id, menuItem);
      elementById.set(menuItem.id, dropdownItem);
      if (parent) {
        parentById.set(menuItem.id, parent);
      }

      return dropdownItem;
    });
  }

  function toggleDropdown(e: MouseEvent) {
    e.preventDefault();
    e.stopPropagation();

    if (isOpen) {
      closeDropdown();
    } else {
      openDropdown();
    }
  }

  function openDropdown() {
    isOpen = true;
    menu.classList.add('show');
    button.classList.add('open');
    button.setAttribute('aria-expanded', 'true');
    updateFocusableItems();
    currentFocus = -1;

    if (options.highlightSelectedValue === true) {
      elementById.values().forEach((el) => {
        el.classList.remove('selected');
      });

      if (selectedItem) {
        const el = elementById.get(selectedItem.id);
        if (el) {
          el.classList.add('selected');

          let parent = parentById.get(selectedItem.id);
          while (parent) {
            const parentEl = elementById.get(parent.id);
            if (parentEl) {
              parentEl.classList.add('selected');
            }

            parent = parentById.get(parent.id);
          }
        }
      }
    }

    document.addEventListener('click', handleOutsideClick, true);
  }

  function closeDropdown() {
    if (!isOpen) return;

    isOpen = false;
    menu.classList.remove('show');
    button.classList.remove('open');
    button.setAttribute('aria-expanded', 'false');
    hideAllSubmenus();
    clearActiveStates();
    button.focus();
    currentFocus = -1;

    document.removeEventListener('click', handleOutsideClick, true);
  }

  function hideAllSubmenus() {
    elementById.values().forEach((el) => {
      el.classList.remove('active', 'keyboard-active');
      el.setAttribute('aria-expanded', 'false');
    });
  }

  function clearActiveStates() {
    focusableItems.forEach((item) => {
      item.classList.remove('active', 'keyboard-active');
    });
  }

  function updateFocusableItems(itemId?: string) {
    let items: NestedSelectItem[] = [];

    focusableItems = [];

    if (!itemId) {
      currentFocus = -1;
      items = menuData;
    } else {
      const item = itemById.get(itemId);
      if (item) {
        items = item.children ?? [];
      } else {
        items = menuData;
      }
    }
    items.forEach((item) => {
      const el = elementById.get(item.id);
      if (el) {
        focusableItems.push(el);
      }
    });
  }

  function handleButtonKeydown(e: KeyboardEvent) {
    switch (e.key) {
      case 'Enter':
      case ' ':
      case 'ArrowDown':
        e.preventDefault();
        if (!isOpen) {
          openDropdown();
        }
        updateFocusableItems();
        if (focusableItems.length > 0) {
          setFocus(0);
        }
        break;
      case 'ArrowUp':
        e.preventDefault();
        if (!isOpen) {
          openDropdown();
        }
        updateFocusableItems();
        if (focusableItems.length > 0) {
          setFocus(focusableItems.length - 1);
        }
        break;
      case 'Escape':
        if (isOpen) {
          e.stopImmediatePropagation();
          e.stopPropagation();
          e.preventDefault();
          closeDropdown();
        }
        break;
    }
  }

  function handleItemKeydown(e: KeyboardEvent, item: NestedSelectItem) {
    e.stopPropagation();
    e.stopImmediatePropagation();

    switch (e.key) {
      case 'Enter':
      case ' ':
        e.preventDefault();
        if ('value' in item) {
          selectItem(item);
        } else {
          openSubmenuKeyboard(item);
        }
        break;
      case 'ArrowDown':
        e.preventDefault();
        navigateDown();
        break;
      case 'ArrowUp':
        e.preventDefault();
        navigateUp();
        break;
      case 'ArrowRight':
        e.preventDefault();
        if (!('value' in item)) {
          openSubmenuKeyboard(item);
        }
        break;
      case 'ArrowLeft':
        e.preventDefault();
        closeSubmenuKeyboard(item);
        break;
      case 'Escape':
        if (isOpen) {
          e.preventDefault();
          closeDropdown();
        }
        break;
    }
  }

  function openSubmenuKeyboard(item: NestedSelectItem) {
    if (!item.children || item.children.length === 0) {
      return;
    }

    const el = elementById.get(item.id);
    if (!el) {
      return;
    }

    el.classList.add('keyboard-active');

    updateFocusableItems(item.id);
    setFocus(0);
  }

  function navigateDown() {
    if (currentFocus < focusableItems.length - 1) {
      setFocus(currentFocus + 1);
    } else {
      setFocus(0);
    }
  }

  function navigateUp() {
    if (currentFocus > 0) {
      setFocus(currentFocus - 1);
    } else {
      setFocus(focusableItems.length - 1);
    }
  }

  function setFocus(index: number) {
    clearActiveStates();
    currentFocus = index;
    if (focusableItems[index]) {
      focusableItems[index].focus();
      focusableItems[index].classList.add('active');
    }
  }

  function closeSubmenuKeyboard(item: NestedSelectItem) {
    const parent = parentById.get(item.id);
    if (!parent) {
      return;
    }

    const el = elementById.get(parent.id);
    if (!el) {
      return;
    }

    const grandParent = parentById.get(parent.id);

    el.classList.remove('keyboard-active');
    updateFocusableItems(grandParent?.id);

    const index = focusableItems.indexOf(el);
    setFocus(index >= 0 ? index : 0);
  }

  function selectItem(item: NestedSelectItem) {
    if (options.showSelectedValue === true) {
      const span = button.querySelector('span');
      if (span) {
        span.textContent = item.name;
      }
    }

    closeDropdown();
    button.focus();

    selectedItem = item;
    onSelect(item);
  }

  function handleOutsideClick(e: MouseEvent) {
    if (!e.composedPath().includes(dropdownContainer)) {
      closeDropdown();
    }
  }

  return dropdownContainer;
}
