import { chats } from '../../chats';
import { executor } from '../../executor';
import { globalState } from '../../globalState';
import { lng } from '../../lng';
import { confirmDialog } from '../cofirmDialog';
import { button } from '../dom/button';
import { icon } from '../dom/icon';
import { tooltip } from '../dom/tooltip';
import {
  copy,
  edit,
  elipsisVertical,
  filledLock,
  ghostImage,
  ghostText,
  globe,
  image,
  loadingCircle,
  lock,
  lockCircle,
  lockOpen,
  panelLeftClose,
  panelLeftOpen,
  pin,
  plus,
  text,
  trash,
  unpin,
  user,
} from '../icons';
import { createElement } from '../utils';
import { emitter } from './emitter';
import { showPortalDropdown, type DropdownItem } from './portal/portalDropdown';
import {
  cloneChat,
  closeSidebar,
  openSidebar,
  pinChat,
  renameChat,
  selectChat,
  setPrivateChat as setPublicStatusChat,
} from './sidebarActions';
import { toggleButton } from '../dom/toggleButton';

import type { Chat, RenderedChat } from '../../chats';

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

export type ChatEl = HTMLDivElement & {
  setTitle: (title: string) => void;
};

export type Sidebar = HTMLDivElement & {
  addChat: (el: HTMLElement) => void;
  deleteChats: () => void;
  renderChats: (groupedChats: Record<string, RenderedChat[]>) => void;
  chats: HTMLDivElement;
  sidebar: HTMLDivElement;
};

const createChatActionDialog = (
  title: string,
  content: string | HTMLElement,
  confirmText: string,
  onConfirm: () => void,
  onLoad?: () => void,
  focusTarget?: HTMLElement,
) => {
  return confirmDialog({
    title,
    content,
    confirmText,
    onConfirm: () => {
      onConfirm();
      if (focusTarget) {
        requestAnimationFrame(() => {
          focusTarget.focus();
          focusTarget.blur();
        });
      }
    },
    onCancel: () => {
      if (focusTarget) {
        requestAnimationFrame(() => {
          focusTarget.focus();
          focusTarget.blur();
        });
      }
    },
    onLoad,
  });
};

const preventDefaultMouseBehavior = (element: HTMLElement) => {
  element.addEventListener('mousedown', (e) => {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
  });
};

export const buildSidebar = () => {
  const wrapper = createElement('div', 'sidebarWrapper', []) as Sidebar;
  const sidebar = createElement('div', 'sidebar', [], {
    role: 'dialog',
    ariaLabel: lng('modai.ui.chats_sidebar'),
    ariaModal: 'false',
    tabIndex: -1,
  });

  sidebar.addEventListener('click', (e) => {
    e.stopPropagation();
    e.stopImmediatePropagation();
  });

  wrapper.addEventListener('click', () => {
    closeSidebar();
  });

  const closeTooltip = tooltip(lng('modai.ui.close_chats'));
  closeTooltip.style.left = '80%';

  const sidebarButton = button([icon(24, panelLeftClose), closeTooltip], closeSidebar, undefined, {
    ariaLabel: lng('modai.ui.close_chats'),
  });

  const newChatBtn = button(
    [icon(24, plus), tooltip(lng('modai.ui.new_chat'))],
    () => {
      emitter.emit('chat:new', { public: true });
    },
    undefined,
    {
      ariaLabel: lng('modai.ui.new_chat'),
    },
  );

  const newPrivateChatBtn = button(
    [icon(24, lockCircle), tooltip(lng('modai.ui.new_private_chat'))],
    () => {
      emitter.emit('chat:new', { public: false });
    },
    undefined,
    {
      ariaLabel: lng('modai.ui.new_private_chat'),
    },
  );

  preventDefaultMouseBehavior(newChatBtn);
  preventDefaultMouseBehavior(newPrivateChatBtn);

  newChatBtn.disable();

  globalState.modal.modalButtons.push(sidebarButton);
  globalState.modal.actionButtons.push(newChatBtn);
  globalState.modal.actionButtons.push(newPrivateChatBtn);

  const publicButtonToggle = toggleButton(
    {
      states: [
        {
          name: 'public',
          icon: globe,
          label: 'Show Public Chats',
        },
        {
          name: 'my',
          icon: user,
          label: 'Show My Chats',
        },
        {
          name: 'private',
          icon: lock,
          label: 'Show Private Chats',
        },
      ] as const,
      defaultState: 'public',
    },
    async (state) => {
      chats.setFilter({ name: 'chatType', value: state.name });
      await chats.init();
    },
  );

  const leftButtons = createElement('div', 'buttonsWrapper', [
    sidebarButton,
    newChatBtn,
    newPrivateChatBtn,
  ]);
  const rightButtons = createElement('div', 'buttonsWrapper', [publicButtonToggle]);

  const header = createElement('header', 'header', [leftButtons, rightButtons]);

  const search = createElement('div', 'searchWrapper');
  const searchInput = createElement('input', undefined, undefined, {
    placeholder: lng('modai.ui.chat_search'),
  });

  searchInput.addEventListener('input', async (e) => {
    const target = e.target as HTMLInputElement;
    loadingIcon.style.display = '';
    const data = await executor.chat.searchChats(target.value);
    chats.searchChats(data.chats);
    loadingIcon.style.display = 'none';
  });

  const loadingIcon = icon(16, loadingCircle);
  loadingIcon.classList.add('spinner');
  loadingIcon.style.display = 'none';

  search.append(searchInput);
  search.append(loadingIcon);

  const body = createElement('div', 'groupedChats', []);

  sidebar.append(header);
  sidebar.append(search);
  sidebar.append(body);

  wrapper.append(sidebar);

  const headerButtons = [sidebarButton, newChatBtn, newPrivateChatBtn];
  const chatFocusableElements: HTMLElement[] = [];
  let currentlyFocusedElement: HTMLElement | null = null;

  const getFocusableElements = (): HTMLElement[] => {
    const elements: HTMLElement[] = [];

    headerButtons.forEach((button) => {
      if (!button.disabled) elements.push(button);
    });

    chatFocusableElements.forEach((element) => {
      if (!element.hasAttribute('disabled') && element.getAttribute('tabindex') !== '-1') {
        elements.push(element);
      }
    });

    return elements;
  };

  const handleFocusChange = (e: FocusEvent) => {
    const target = e.target as HTMLElement;
    const focusableElements = getFocusableElements();

    if (focusableElements.includes(target) || target === sidebar) {
      currentlyFocusedElement = target;
    }
  };

  sidebar.addEventListener('focusin', handleFocusChange);

  const disableTransitions = () => {
    sidebar.classList.add('no-transitions');
    setTimeout(() => {
      sidebar.classList.remove('no-transitions');
    }, 50);
  };

  const trapFocus = (e: KeyboardEvent) => {
    if (!wrapper.classList.contains('open')) {
      return;
    }

    if (e.key === KEYBOARD_KEYS.TAB) {
      const focusableElements = getFocusableElements();

      if (focusableElements.length === 0) {
        return;
      }

      const firstElement = focusableElements[0];
      const lastElement = focusableElements[focusableElements.length - 1];

      if (e.shiftKey) {
        disableTransitions();

        if (currentlyFocusedElement === firstElement) {
          e.preventDefault();
          sidebar.focus();
          return;
        }

        if (currentlyFocusedElement === sidebar) {
          e.preventDefault();
          lastElement.focus();
        }

        return;
      }

      disableTransitions();

      if (currentlyFocusedElement === lastElement) {
        e.preventDefault();
        sidebar.focus();
      }

      return;
    }

    if (e.key === KEYBOARD_KEYS.ESCAPE) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();

      closeSidebar();
    }
  };

  sidebar.addEventListener('keydown', trapFocus);

  wrapper.addChat = (el) => {
    body.append(el);
    const focusableElements = el.querySelectorAll('button, [tabindex="0"]');
    focusableElements.forEach((element) => {
      chatFocusableElements.push(element as HTMLElement);
    });
  };

  wrapper.deleteChats = () => {
    body.innerHTML = '';
    chatFocusableElements.length = 0;
  };

  wrapper.renderChats = (groupedChats) => {
    body.innerHTML = '';
    chatFocusableElements.length = 0;

    const allChats = Object.entries(groupedChats);
    if (allChats.length === 0) {
      const noChatsMessage = createElement('div', 'noChatsMessage', lng('modai.ui.no_chats'));
      body.append(noChatsMessage);
      return;
    }

    for (const [groupName, chats] of allChats) {
      const availableChatss = chats.filter((chat) =>
        globalState.modal.config.availableTypes?.includes(chat.type),
      );

      if (availableChatss.length === 0) {
        continue;
      }

      const groupElement = createElement('div', 'group', [
        createElement('div', 'title', groupName),
        createElement(
          'div',
          'chats',
          availableChatss.map((chat) => chat.el),
        ),
      ]);

      body.append(groupElement);

      availableChatss.forEach((chat) => {
        const focusableElements = chat.el.querySelectorAll('button, [tabindex="0"]');
        focusableElements.forEach((element) => {
          chatFocusableElements.push(element as HTMLElement);
        });
      });
    }
  };

  wrapper.chats = body;
  wrapper.sidebar = sidebar;

  globalState.modal.sidebar = wrapper;

  emitter.on('chat:new', ({ eventData }) => {
    if (eventData.public) {
      newChatBtn.disable();
      newPrivateChatBtn.enable();
    } else {
      newChatBtn.enable();
      newPrivateChatBtn.disable();
    }
  });

  emitter.on('chat:delete', () => {
    newChatBtn.disable();
    newPrivateChatBtn.enable();
  });

  emitter.on('loading', ({ eventData }) => {
    if (eventData.isLoading || !eventData.hasMessages) {
      newChatBtn.disable();
      newPrivateChatBtn.disable();
    } else {
      newChatBtn.enable();
      newPrivateChatBtn.enable();
    }

    if (eventData.isLoading) {
      sidebarButton.disable();
    } else {
      sidebarButton.enable();
    }
  });

  return wrapper;
};

export const buildSidebarControls = () => {
  const sidebarButton = button(
    [icon(24, panelLeftOpen), tooltip(lng('modai.ui.open_chats'))],
    () => {
      openSidebar(sidebarButton);
    },
  );
  sidebarButton.setAttribute('aria-label', lng('modai.ui.open_chats'));

  preventDefaultMouseBehavior(sidebarButton);

  const newChatBtn = button([icon(24, plus), tooltip(lng('modai.ui.new_chat'))], () => {
    emitter.emit('chat:new', { public: true });
  });
  newChatBtn.setAttribute('aria-label', lng('modai.ui.new_chat'));

  preventDefaultMouseBehavior(newChatBtn);

  newChatBtn.disable();

  globalState.modal.actionButtons.push(newChatBtn);
  globalState.modal.modalButtons.push(sidebarButton);

  emitter.on('chat:new', ({ eventData }) => {
    if (eventData.public) {
      newChatBtn.disable();
    } else {
      newChatBtn.enable();
    }
  });

  emitter.on('chat:delete', () => {
    newChatBtn.disable();
  });

  emitter.on('loading', ({ eventData }) => {
    if (eventData.isLoading || !eventData.hasMessages) {
      newChatBtn.disable();
    } else {
      newChatBtn.enable();
    }

    if (eventData.isLoading) {
      sidebarButton.disable();
    } else {
      sidebarButton.enable();
    }
  });

  return [sidebarButton, newChatBtn];
};

const getChatIcon = (chat: Chat) => {
  if (chat.view_only) {
    return icon(20, chat.type === 'text' ? ghostText : ghostImage);
  }

  if (!chat.public) {
    return createElement('div', 'iconStack', [
      icon(18, chat.type === 'text' ? text : image),
      icon(12, filledLock),
    ]);
  }

  return icon(20, chat.type === 'text' ? text : image);
};

export const renderChat = (chat: Chat) => {
  const wrapper = createElement('div', 'wrapper', undefined, { tabIndex: -1 }) as ChatEl;
  const title = createElement('div', 'title');
  title.textContent = chat.title;

  const btn = button(
    [getChatIcon(chat), title],
    () => {
      selectChat(chat.id);
    },
    'chat',
    {
      title: chat.title,
      ariaLabel: lng('modai.ui.select_chat', { title: chat.title }),
      role: 'button',
    },
  );

  btn.addEventListener('keydown', (e) => {
    if (e.key === KEYBOARD_KEYS.ENTER || e.key === KEYBOARD_KEYS.SPACE) {
      e.preventDefault();
      selectChat(chat.id);
    }
  });
  const actions = createElement('div', 'actions');

  const gradient = createElement('div', 'gradient');
  const deleteBtn = button(
    [icon(16, trash), tooltip(lng('modai.ui.delete_chat'))],
    () => {
      const currentChat = chats.getChat(chat.id);
      if (!currentChat) {
        return;
      }

      confirmDialog({
        title: lng('modai.ui.delete_chat_long'),
        content: lng('modai.ui.delete_chat_desc', { title: currentChat.title }),
        confirmText: lng('modai.ui.delete'),
        onConfirm: () => {
          // Find the previous chat to focus after deletion
          const currentWrapper = wrapper;
          const parentGroup = currentWrapper.parentElement;
          const allChatWrappers = parentGroup
            ? Array.from(parentGroup.querySelectorAll('.wrapper'))
            : [];
          const currentIndex = allChatWrappers.indexOf(currentWrapper);

          let targetChatButton: HTMLElement | null = null;

          // Try to find the previous chat button, or next if no previous exists
          if (currentIndex > 0) {
            // Focus previous chat
            const prevWrapper = allChatWrappers[currentIndex - 1] as HTMLElement;
            targetChatButton = prevWrapper.querySelector('button.chat') as HTMLElement;
          } else if (currentIndex === 0 && allChatWrappers.length > 1) {
            // Focus next chat if we're deleting the first one
            const nextWrapper = allChatWrappers[currentIndex + 1] as HTMLElement;
            targetChatButton = nextWrapper.querySelector('button.chat') as HTMLElement;
          }

          emitter.emit('chat:delete', { chatId: chat.id });

          if (targetChatButton) {
            requestAnimationFrame(() => {
              targetChatButton.focus();
            });
          } else {
            requestAnimationFrame(() => {
              globalState.modal.sidebar?.sidebar.focus();
            });
          }
        },
        onCancel: () => {
          requestAnimationFrame(() => {
            wrapper.focus();
            wrapper.blur();
          });
        },
      });
    },
    undefined,
    {
      ariaLabel: lng('modai.ui.delete_chat_long'),
    },
  );

  const pinnedContent = [icon(16, pin), tooltip(lng('modai.ui.pin_chat'))];
  const unpinnedContent = [icon(16, unpin), tooltip(lng('modai.ui.unpin_chat'))];

  const pinBtn = button(
    chat.pinned ? unpinnedContent : pinnedContent,
    async () => {
      const currentChat = chats.getChat(chat.id);
      if (!currentChat) {
        return;
      }

      const newState = !currentChat.pinned;
      pinBtn.disable();
      await executor.chat.pinChat(chat.id, newState);
      pinBtn.innerHTML = '';
      pinBtn.append(...(newState ? unpinnedContent : pinnedContent));
      pinChat(chat.id, newState);
      pinBtn.enable();
    },
    undefined,
    {
      ariaLabel: chat.pinned ? lng('modai.ui.unpin_chat') : lng('modai.ui.pin_chat'),
    },
  );

  const buildDropdownItems = (): DropdownItem[] => {
    const dropdownItems = [];

    dropdownItems.push({
      icon: copy,
      text: lng('modai.ui.clone_chat'),
      onClick: () => {
        const currentChat = chats.getChat(chat.id);
        if (!currentChat) {
          return;
        }

        createChatActionDialog(
          lng('modai.ui.clone_chat_long'),
          lng('modai.ui.clone_chat_desc', { title: currentChat.title }),
          lng('modai.ui.clone'),
          () => {
            cloneChat(chat.id);
          },
          undefined,
          wrapper,
        );
      },
    });

    if (!chat.view_only) {
      dropdownItems.push({
        icon: edit,
        text: lng('modai.ui.rename_chat'),
        onClick: () => {
          const currentChat = chats.getChat(chat.id);
          if (!currentChat) {
            return;
          }

          const contentWrapper = createElement('div', 'formWrapper');
          const input = createElement('input', 'input', undefined, {
            value: currentChat.title,
            type: 'text',
            name: 'title',
          });
          const label = createElement('label', 'label', [input]);

          contentWrapper.append(label);

          const dialog = createChatActionDialog(
            lng('modai.ui.rename_chat_long'),
            contentWrapper,
            lng('modai.ui.save'),
            () => {
              const newTitle = input.value.trim();
              if (!newTitle) {
                return;
              }

              renameChat(currentChat.id, newTitle);
            },
            () => {
              input.focus();
            },
            wrapper,
          );

          input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
              dialog.api.confirmDialog();
            }
          });
        },
      });

      if (chat.public) {
        dropdownItems.push({
          icon: lock,
          text: lng('modai.ui.chat_make_private'),
          onClick: () => {
            const currentChat = chats.getChat(chat.id);
            if (!currentChat) {
              return;
            }

            createChatActionDialog(
              lng('modai.ui.chat_make_private_long'),
              lng('modai.ui.chat_make_private_desc', { title: currentChat.title }),
              lng('modai.ui.save'),
              () => {
                setPublicStatusChat(chat.id, false);
              },
              undefined,
              wrapper,
            );
          },
        });
      } else {
        dropdownItems.push({
          icon: lockOpen,
          text: lng('modai.ui.chat_make_public'),
          onClick: () => {
            const currentChat = chats.getChat(chat.id);
            if (!currentChat) {
              return;
            }

            createChatActionDialog(
              lng('modai.ui.chat_make_public_long'),
              lng('modai.ui.chat_make_public_desc', { title: currentChat.title }),
              lng('modai.ui.save'),
              () => {
                setPublicStatusChat(chat.id, true);
              },
              undefined,
              wrapper,
            );
          },
        });
      }
    }

    return dropdownItems;
  };

  const moreBtn = button(
    [icon(16, elipsisVertical), tooltip(lng('modai.ui.more_actions'))],
    (e) => {
      e.stopPropagation();
      e.stopImmediatePropagation();
      e.preventDefault();

      showPortalDropdown(
        moreBtn,
        buildDropdownItems(),
        wrapper,
        globalState.modal.sidebar!,
        globalState.modal.modal.getBoundingClientRect(),
      );
    },
    undefined,
    {
      ariaLabel: lng('modai.ui.more_actions'),
      ariaHasPopup: 'true',
      ariaExpanded: 'false',
    },
  );

  moreBtn.addEventListener('keydown', (e) => {
    if (
      e.key === KEYBOARD_KEYS.ENTER ||
      e.key === KEYBOARD_KEYS.SPACE ||
      e.key === KEYBOARD_KEYS.ARROW_DOWN
    ) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();

      showPortalDropdown(
        moreBtn,
        buildDropdownItems(),
        wrapper,
        globalState.modal.sidebar!,
        globalState.modal.modal.getBoundingClientRect(),
      );
    }
  });

  preventDefaultMouseBehavior(moreBtn);

  if (!chat.view_only) {
    actions.append(pinBtn);
    actions.append(deleteBtn);
    actions.append(moreBtn);
    actions.append(gradient);
  } else {
    const cloneBtn = button(
      [icon(16, copy), tooltip(lng('modai.ui.clone_chat'))],
      () => {
        const currentChat = chats.getChat(chat.id);
        if (!currentChat) {
          return;
        }

        createChatActionDialog(
          lng('modai.ui.clone_chat_long'),
          lng('modai.ui.clone_chat_desc', { title: currentChat.title }),
          lng('modai.ui.clone'),
          () => {
            cloneChat(chat.id);
          },
          undefined,
          btn,
        );
      },
      undefined,
      {
        ariaLabel: lng('modai.ui.delete_chat_long'),
      },
    );

    actions.append(cloneBtn);
  }

  wrapper.append(btn);
  wrapper.append(actions);

  wrapper.setTitle = (newTitle) => {
    title.textContent = newTitle;
  };

  return wrapper;
};
