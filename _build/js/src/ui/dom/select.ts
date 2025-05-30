import { lng } from '../../lng';
import { createElement } from '../utils';
import { icon } from './icon';

type DataItem<IdKey extends string = 'id', DisplayKey extends string = 'name'> = {
  [key in IdKey]: string | number;
} & {
  [key in DisplayKey]: string | number;
} & Record<string, unknown>;

type SelectOption<T extends DataItem<string, string>> = T | null;

export type Select = HTMLDivElement & {
  enable: () => void;
  disable: () => void;
};

export const buildSelect = <
  IdKey extends string,
  DisplayKey extends string,
  T extends DataItem<IdKey, DisplayKey>,
>(
  data: Record<string, T>,
  initialValueId: string | number | null | undefined,
  onSelect: (selectedItem: SelectOption<T>) => void,
  options?: {
    idProperty?: IdKey;
    displayProperty?: DisplayKey;
    noSelectionText?: string;
    selectText?: string;
    icon?: string;
    iconSize?: number;
    nullOptionDisplayText?: string;
    tooltip?: string;
  },
) => {
  const idKey: IdKey = options?.idProperty ?? ('id' as IdKey);
  const displayKey: DisplayKey = options?.displayProperty ?? ('name' as DisplayKey);

  const config = {
    idProperty: idKey,
    displayProperty: displayKey,
    noSelectionText: options?.noSelectionText ?? '',
    selectText: options?.selectText ?? lng('modai.ui.select_item'),
    icon: options?.icon,
    iconSize: options?.iconSize ?? 24,
    nullOptionDisplayText: options?.nullOptionDisplayText,
    tooltip: options?.tooltip ?? lng('modai.ui.select_item'),
  };

  const container = createElement('div', 'selectContainer') as Select;

  let selectedItem: SelectOption<T> = null;
  if (initialValueId !== null && initialValueId !== undefined) {
    selectedItem = data[initialValueId] || null;
  }

  let isOpen = false;
  let focusedIndex = -1;

  const itemList: SelectOption<T>[] = [null, ...Object.values(data)];

  const button = createElement('button', 'selectButton', [], {
    type: 'button',
    ariaHasPopup: 'listbox',
    ariaExpanded: 'false',
    ariaLabel: config.selectText,
  });

  container.enable = () => {
    button.disabled = false;
  };

  container.disable = () => {
    button.disabled = true;
  };

  const updateButtonContent = () => {
    const content: (HTMLElement | string)[] = [];
    let ariaLabel;

    if (config.icon) {
      content.push(icon(config.iconSize, config.icon));
    }

    if (selectedItem) {
      const displayValue = String(selectedItem[config.displayProperty]);
      content.push(createElement('span', 'selectedItemName', displayValue));
      ariaLabel = displayValue;
    } else {
      if (config.noSelectionText) {
        content.push(createElement('span', 'selectedItemName', config.noSelectionText));
      }
      ariaLabel = config.noSelectionText || lng('modai.ui.no_selection');
    }

    button.innerHTML = '';
    button.append(...content);
    button.append(createElement('span', 'tooltip', config.tooltip));
    button.setAttribute('aria-label', ariaLabel);
  };

  const dropdown = createElement('ul', 'selectDropdown', [], {
    role: 'listbox',
    tabIndex: -1,
    ariaHidden: 'true',
  });
  dropdown.classList.add('hidden');

  const optionElements = itemList.map((itemOpt, index) => {
    let displayValue: string | HTMLElement;
    if (itemOpt === null) {
      displayValue = config.nullOptionDisplayText ?? config.noSelectionText;
    } else {
      displayValue = String(itemOpt[config.displayProperty]);
    }

    const isSelected =
      (itemOpt && selectedItem && itemOpt[config.idProperty] === selectedItem[config.idProperty]) ||
      (!itemOpt && !selectedItem);

    const li = createElement('li', 'selectOption', displayValue, {
      role: 'option',
      id: `select-option-${index}`,
      ariaSelected: isSelected ? 'true' : 'false',
      tabIndex: -1,
    });

    li.addEventListener('click', () => {
      selectOption(itemOpt);
      closeDropdown();
    });

    li.addEventListener('keydown', (e) => {
      if (!isOpen) return;

      switch (e.key) {
        case 'ArrowDown':
          e.preventDefault();
          focusOption((index + 1) % optionElements.length);
          break;
        case 'ArrowUp':
          e.preventDefault();
          focusOption((index - 1 + optionElements.length) % optionElements.length);
          break;
        case 'Enter':
        case ' ':
          e.preventDefault();
          selectOption(itemOpt);
          closeDropdown();
          break;
        case 'Escape':
          e.preventDefault();
          e.stopPropagation();
          closeDropdown();
          break;
        case 'Tab':
          closeDropdown();
          break;
        case 'Home':
          e.preventDefault();
          focusOption(0);
          break;
        case 'End':
          e.preventDefault();
          focusOption(optionElements.length - 1);
          break;
        default:
          break;
      }
    });

    return li;
  });

  dropdown.append(...optionElements);

  const openDropdown = () => {
    if (isOpen) return;

    isOpen = true;
    dropdown.classList.remove('hidden');
    dropdown.setAttribute('aria-hidden', 'false');
    button.setAttribute('aria-expanded', 'true');

    focusedIndex = itemList.findIndex((opt) => {
      return (
        (opt && selectedItem && opt[config.idProperty] === selectedItem[config.idProperty]) ||
        (!opt && !selectedItem)
      );
    });

    if (focusedIndex === -1) focusedIndex = 0;

    focusOption(focusedIndex);
    document.addEventListener('click', handleClickOutside, true);
  };

  const closeDropdown = () => {
    if (!isOpen) return;

    isOpen = false;
    dropdown.classList.add('hidden');
    dropdown.setAttribute('aria-hidden', 'true');
    button.setAttribute('aria-expanded', 'false');
    button.focus();
    focusedIndex = -1;
    document.removeEventListener('click', handleClickOutside, true);
  };

  const selectOption = (itemOpt: SelectOption<T>) => {
    selectedItem = itemOpt;
    updateButtonContent();
    optionElements.forEach((li, index) => {
      const currentItem = itemList[index];
      const isSelected =
        (currentItem &&
          selectedItem &&
          currentItem[config.idProperty] === selectedItem[config.idProperty]) ||
        (!currentItem && !selectedItem);
      li.setAttribute('aria-selected', isSelected ? 'true' : 'false');
    });
    onSelect(selectedItem);
  };

  const focusOption = (index: number) => {
    if (index < 0 || index >= optionElements.length) return;

    optionElements.forEach((opt) => {
      opt.classList.remove('focused');
      opt.tabIndex = -1;
    });

    const targetOption = optionElements[index];
    targetOption.tabIndex = 0;
    targetOption.classList.add('focused');
    targetOption.scrollIntoView({ block: 'nearest' });
    targetOption.focus();
    dropdown.setAttribute('aria-activedescendant', targetOption.id);
    focusedIndex = index;
  };

  const handleClickOutside = (event: MouseEvent) => {
    if (!event.composedPath().includes(container)) {
      closeDropdown();
    }
  };

  button.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    if (isOpen) {
      closeDropdown();
      return;
    }

    openDropdown();
  });

  button.addEventListener('keydown', (e) => {
    switch (e.key) {
      case 'Enter':
      case ' ':
      case 'ArrowDown':
        e.preventDefault();
        openDropdown();
        break;
      case 'ArrowUp':
        e.preventDefault();
        openDropdown();
        focusOption(optionElements.length - 1);
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
  });

  updateButtonContent();
  container.append(button, dropdown);

  return container;
};
