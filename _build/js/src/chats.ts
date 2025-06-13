import { executor } from './executor';
import { globalState } from './globalState';
import { lng } from './lng';
import { renderChat } from './ui/localChat/sidebar';

import type { ChatEl } from './ui/localChat/sidebar';

export type Chat = {
  id: number;
  last_message_on: number;
  pinned: boolean;
  public: boolean;
  view_only: boolean;
  title: string;
  type: 'text' | 'image';
};

export type RenderedChat = Chat & { el: ChatEl };

type Store = {
  inited: boolean;
  stale: boolean;
  filters: {
    chatIDs?: Record<number, number>;
    chatType?: 'public' | 'private' | 'my';
  };
  chatById: Record<number, RenderedChat>;
  chats: Record<string, RenderedChat[]>;
};

type ChatFilter = {
  name: Exclude<keyof Store['filters'], 'chatIDs'>;
  value: NonNullable<Store['filters'][Exclude<keyof Store['filters'], 'chatIDs'>]>;
};

const store: Store = {
  inited: false,
  stale: false,
  filters: {},
  chatById: {},
  chats: {},
};

const now = new Date();
const todayStr = now.toISOString().slice(0, 10);
const yesterday = new Date(now);
yesterday.setDate(now.getDate() - 1);
const yesterdayStr = yesterday.toISOString().slice(0, 10);

const getGroupKeyForChat = (chat: Chat) => {
  const date = new Date(chat.last_message_on);
  const dateStr = date.toISOString().slice(0, 10);

  if (chat.pinned) {
    return lng('modai.ui.pinned');
  }

  if (dateStr === todayStr) {
    return lng('modai.ui.today');
  }

  if (dateStr === yesterdayStr) {
    return lng('modai.ui.yesterday');
  }

  const month = date.toLocaleString('default', { month: 'long' });
  const year = date.getFullYear();
  return `${month} ${year}`;
};

const sortChats = () => {
  if (!globalState.modal.sidebar) {
    return;
  }

  const allChats = Object.values(store.chatById).sort((a, b) => {
    if (a.pinned !== b.pinned) {
      return Number(b.pinned) - Number(a.pinned);
    }
    return b.last_message_on - a.last_message_on;
  });

  const newChats: Record<string, RenderedChat[]> = {};
  for (const chat of allChats) {
    const groupKey = getGroupKeyForChat(chat);

    if (!newChats[groupKey]) {
      newChats[groupKey] = [];
    }

    newChats[groupKey].push(store.chatById[chat.id]);
  }

  store.chats = newChats;

  globalState.modal.sidebar.renderChats(store.chats);
};

const filterChats = () => {
  const filteredChats: Record<string, RenderedChat[]> = {};

  for (const [groupKey, chatsInGroup] of Object.entries(store.chats)) {
    const filtered = chatsInGroup.filter((chat) => {
      if (store.filters.chatIDs && store.filters.chatIDs[chat.id] === undefined) {
        return false;
      }

      if (store.filters.chatType === 'my' && chat.view_only) {
        return false;
      }

      if (store.filters.chatType === 'private' && chat.public) {
        return false;
      }

      return true;
    });

    if (filtered.length > 0) {
      filteredChats[groupKey] = filtered;
    }
  }

  return filteredChats;
};

export const chats = {
  init: async () => {
    if (!globalState.modal.sidebar) {
      return;
    }

    if (store.inited && !store.stale) {
      globalState.modal.sidebar.renderChats(filterChats());
      return;
    }

    if (store.stale) {
      globalState.modal.sidebar.deleteChats();
      store.chatById = {};
      store.chats = {};
    }

    const data = await executor.chat.loadChats();
    for (const chatData of data.chats) {
      const chat = {
        ...chatData,
        el: renderChat(chatData),
      };

      store.chatById[chat.id] = chat;

      const groupKey = getGroupKeyForChat(chat);

      if (!store.chats[groupKey]) {
        store.chats[groupKey] = [];
      }

      store.chats[groupKey].push(store.chatById[chat.id]);
    }

    store.inited = true;
    store.stale = false;

    globalState.modal.sidebar.renderChats(filterChats());
  },

  sortChats,

  markAsStale: () => {
    store.stale = true;
  },
  getChat: (id: number) => {
    return store.chatById[id];
  },
  getChats: () => {
    return store.chats;
  },
  deleteChat: (id: number) => {
    delete store.chatById[id];
  },
  searchChats: async (chatIDs: Record<number, number>) => {
    store.filters.chatIDs = chatIDs;
    await chats.init();
  },
  setFilter: (filter: ChatFilter) => {
    store.filters[filter.name] = filter.value;
  },
  clearFilters: () => {
    store.filters = {};
  },
};
