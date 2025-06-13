import { emitter } from './emitter';
import { showWelcomeMessage, switchType } from './modalActions';
import { chats } from '../../chats';
import { createOrUpdateChat, deleteChatByChatId, deleteChatByKeyAndUserId } from '../../db';
import { executor } from '../../executor';
import { globalState } from '../../globalState';

emitter.on('chat:new', ({ eventData }) => {
  showWelcomeMessage();
  globalState.modal.chatPublic = eventData.public;
  globalState.modal.chatId = undefined;
  globalState.modal.history.switchChatId(undefined);
  globalState.modal.setTitle(undefined);
  globalState.modal.enableSending();

  void deleteChatByKeyAndUserId(globalState.modal.history.getKey(), globalState.config.user.id);

  closeSidebar();
});

emitter.on('chat:delete', ({ eventData: { chatId } }) => {
  if (globalState.modal.chatId === chatId) {
    showWelcomeMessage();
    globalState.modal.chatId = undefined;
    globalState.modal.setTitle(undefined);
  }

  void deleteChatByChatId(chatId);
  void executor.chat.deleteChat(chatId);
  globalState.modal.history.clearHistory();
  chats.deleteChat(chatId);
  chats.sortChats();
});

export const selectChat = async (chatId: number) => {
  const chat = chats.getChat(chatId);
  if (!chat) {
    return;
  }

  if (globalState.modal.config.type !== chat.type) {
    const modeButton = globalState.modal.modeButtons.find((btn) => btn.mode === chat.type);
    if (!modeButton) {
      return;
    }
    globalState.modal.chatId = chatId;
    modeButton.activate();

    const config = globalState.modal.config;
    await createOrUpdateChat(
      `${config.namespace ?? 'modai'}/${config.key}/${chat.type}`,
      chatId,
      globalState.config.user.id,
    );

    switchType(chat.type);
    closeSidebar();

    return;
  }

  globalState.modal.chatId = chatId;
  globalState.modal.history.switchChatId(chatId);

  closeSidebar();
};

let sidebarOpener: HTMLElement | null = null;

export const openSidebar = (opener?: HTMLElement) => {
  void chats.init();
  globalState.modal.sidebar?.classList.add('open');

  sidebarOpener = opener || null;

  if (globalState.modal.sidebar) {
    const sidebarElement = globalState.modal.sidebar.sidebar;
    requestAnimationFrame(() => {
      sidebarElement.focus();
    });
  }
};

export const closeSidebar = () => {
  globalState.modal.sidebar?.classList.remove('open');

  if (globalState.modal.portal) {
    globalState.modal.portal.innerHTML = '';
  }

  if (sidebarOpener && document.contains(sidebarOpener)) {
    sidebarOpener.focus();
  }

  sidebarOpener = null;
};

export const pinChat = (chatId: number, pinned: boolean) => {
  const chat = chats.getChat(chatId);
  if (!chat) {
    return;
  }
  chat.pinned = pinned;

  chats.sortChats();
};

export const setPrivateChat = async (chatId: number, publicStatus: boolean) => {
  const chat = chats.getChat(chatId);
  if (!chat) {
    return;
  }

  await executor.chat.setPublicChat(chatId, publicStatus);

  chat.public = publicStatus;

  chats.markAsStale();
  void chats.init();
};

export const renameChat = (chatId: number, newTitle: string) => {
  const chat = chats.getChat(chatId);
  if (!chat) {
    return;
  }

  chat.title = newTitle;
  chat.el.setTitle(newTitle);

  void executor.chat.setChatTitle(chatId, newTitle);

  chats.sortChats();
};

export const cloneChat = async (chatId: number) => {
  const chat = chats.getChat(chatId);
  if (!chat) {
    return;
  }

  await executor.chat.cloneChat(chatId);

  chats.markAsStale();
  void chats.init();
};
