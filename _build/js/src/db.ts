import type {
  AssistantMessageContentType,
  Message,
  UserAttachment,
  UserMessageContext,
  BaseMessage,
} from './chatHistory';
import type { Metadata, ToolCalls, ToolResponseContent } from './executor/types';

const DB_NAME = 'modAI';
const OBJECT_STORE_NAME = 'messages';

let db: IDBDatabase | null = null;

type BaseDBMessage = BaseMessage & {
  timestamp: number;
  category: string;
};

type ToolResponseMessage = BaseDBMessage & {
  __type: 'ToolResponseMessage';
  role: 'tool';
  content: ToolResponseContent;
};

type AssistantMessage = Metadata &
  Omit<BaseDBMessage, 'toolCalls'> & {
    __type: 'AssistantMessage';
    role: 'assistant';
    content: string | undefined;
    contentType: AssistantMessageContentType;
    toolCalls?: ToolCalls;
  };

type UserMessage = Omit<BaseDBMessage, 'contexts' | 'attachments'> & {
  __type: 'UserMessage';
  role: 'user';
  content: string;
  contexts?: UserMessageContext[];
  attachments?: UserAttachment[];
};

type DBMessage = UserMessage | AssistantMessage | ToolResponseMessage;

const initializeDatabase = async (): Promise<IDBDatabase> => {
  return new Promise((resolve, reject) => {
    if (db) {
      resolve(db);
      return;
    }

    const request = indexedDB.open(DB_NAME, 1);

    request.onupgradeneeded = (event) => {
      const db = (event.target as IDBOpenDBRequest).result;
      if (!db.objectStoreNames.contains(OBJECT_STORE_NAME)) {
        const objectStore = db.createObjectStore(OBJECT_STORE_NAME, { keyPath: 'id' });

        objectStore.createIndex('timestamp', 'timestamp', { unique: false });
        objectStore.createIndex('category', 'category', { unique: false });
      }
    };

    request.onsuccess = (event) => {
      db = (event.target as IDBOpenDBRequest).result;
      resolve(db);
    };

    request.onerror = (event) => {
      reject((event.target as IDBOpenDBRequest).error);
    };
  });
};

export const getMessages = async (category: string): Promise<DBMessage[]> => {
  const db = await initializeDatabase();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(OBJECT_STORE_NAME, 'readonly');
    const objectStore = transaction.objectStore(OBJECT_STORE_NAME);
    const index = objectStore.index('category');
    const request = index.getAll(category);

    request.onsuccess = (event) => {
      const messages = (event.target as IDBRequest<DBMessage[]>).result || [];
      messages.sort((a, b) => a.timestamp - b.timestamp);
      resolve(messages);
    };

    request.onerror = (event) => {
      reject((event.target as IDBRequest<DBMessage[]>).error);
    };
  });
};

export const deleteMessagesAfter = async (
  category: string,
  editedMessageId: string,
): Promise<void> => {
  const db = await initializeDatabase();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(OBJECT_STORE_NAME, 'readwrite');
    const objectStore = transaction.objectStore(OBJECT_STORE_NAME);
    const index = objectStore.index('category');
    const getAllRequest = index.getAll(category);

    getAllRequest.onsuccess = async (event) => {
      const allMessages = (event.target as IDBRequest<DBMessage[]>).result || [];
      allMessages.sort((a, b) => a.timestamp - b.timestamp);
      const editedMessageIndex = allMessages.findIndex((msg) => msg.id === editedMessageId);

      if (editedMessageIndex !== -1) {
        for (let i = editedMessageIndex; i < allMessages.length; i++) {
          const deleteRequest = objectStore.delete(allMessages[i].id);
          deleteRequest.onerror = (deleteEvent) => {
            reject((deleteEvent.target as IDBRequest).error);
            return;
          };
        }
      }

      transaction.oncomplete = () => {
        resolve();
      };

      transaction.onerror = (transactionEvent) => {
        reject((transactionEvent.target as IDBTransaction).error);
      };
    };

    getAllRequest.onerror = (event) => {
      reject((event.target as IDBRequest).error);
    };
  });
};

export const deleteAllMessages = async (category: string): Promise<void> => {
  const db = await initializeDatabase();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(OBJECT_STORE_NAME, 'readwrite');
    const objectStore = transaction.objectStore(OBJECT_STORE_NAME);
    const index = objectStore.index('category');
    const getAllRequest = index.getAll(category);

    getAllRequest.onsuccess = async (event) => {
      const allMessages = (event.target as IDBRequest<DBMessage[]>).result || [];
      allMessages.sort((a, b) => a.timestamp - b.timestamp);
      for (let i = 0; i < allMessages.length; i++) {
        const deleteRequest = objectStore.delete(allMessages[i].id);
        deleteRequest.onerror = (deleteEvent) => {
          reject((deleteEvent.target as IDBRequest).error);
          return;
        };
      }

      transaction.oncomplete = () => {
        resolve();
      };

      transaction.onerror = (transactionEvent) => {
        reject((transactionEvent.target as IDBTransaction).error);
      };
    };

    getAllRequest.onerror = (event) => {
      reject((event.target as IDBRequest).error);
    };
  });
};

export const saveMessage = async (category: string, newMessage: Message): Promise<void> => {
  const db = await initializeDatabase();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(OBJECT_STORE_NAME, 'readwrite');
    const objectStore = transaction.objectStore(OBJECT_STORE_NAME);

    let request;

    if (newMessage.__type === 'UserMessage') {
      const msg: DBMessage = {
        __type: 'UserMessage',
        timestamp: Date.now(),
        category,
        id: newMessage.id,
        content: newMessage.content,
        role: newMessage.role,
        toolCalls: newMessage.toolCalls,
        contexts: newMessage.contexts,
        attachments: newMessage.attachments,
        hidden: newMessage.hidden,
        ctx: newMessage.ctx,
      };

      request = objectStore.put(msg);
    }

    if (newMessage.__type === 'AssistantMessage') {
      const msg: DBMessage = {
        __type: 'AssistantMessage',
        timestamp: Date.now(),
        category,
        id: newMessage.id,
        content: newMessage.content,
        role: newMessage.role,
        toolCalls: newMessage.toolCalls,
        contexts: newMessage.contexts,
        attachments: newMessage.attachments,
        hidden: newMessage.hidden,
        ctx: newMessage.ctx,
        contentType: newMessage.contentType,
        metadata: newMessage.metadata,
      };

      request = objectStore.put(msg);
    }

    if (newMessage.__type === 'ToolResponseMessage') {
      const msg: DBMessage = {
        __type: 'ToolResponseMessage',
        timestamp: Date.now(),
        category,
        id: newMessage.id,
        content: newMessage.content,
        role: newMessage.role,
        toolCalls: newMessage.toolCalls,
        contexts: newMessage.contexts,
        attachments: newMessage.attachments,
        hidden: newMessage.hidden,
        ctx: newMessage.ctx,
      };

      request = objectStore.put(msg);
    }

    if (!request) {
      return;
    }

    request.onsuccess = () => {
      resolve();
    };

    request.onerror = (event) => {
      reject((event.target as IDBRequest).error);
    };
  });
};

export const updateMessage = async (message: Message): Promise<void> => {
  const db = await initializeDatabase();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(OBJECT_STORE_NAME, 'readwrite');
    const objectStore = transaction.objectStore(OBJECT_STORE_NAME);

    const getRequest = objectStore.get(message.id);

    getRequest.onsuccess = (event) => {
      const existingMessage = (event.target as IDBRequest<Message>).result;

      if (existingMessage) {
        let putRequest;

        if (message.__type === 'UserMessage') {
          const msg = {
            ...existingMessage,
            content: message.content,
            role: message.role,
            toolCalls: message.toolCalls,
            contexts: message.contexts,
            attachments: message.attachments,
            hidden: message.hidden,
            ctx: message.ctx,
          };

          putRequest = objectStore.put(msg);
        }

        if (message.__type === 'AssistantMessage') {
          const msg = {
            ...existingMessage,
            content: message.content,
            role: message.role,
            toolCalls: message.toolCalls,
            contexts: message.contexts,
            attachments: message.attachments,
            hidden: message.hidden,
            ctx: message.ctx,
            contentType: message.contentType,
            metadata: message.metadata,
          };

          putRequest = objectStore.put(msg);
        }

        if (message.__type === 'ToolResponseMessage') {
          const msg = {
            ...existingMessage,
            content: message.content,
            role: message.role,
            toolCalls: message.toolCalls,
            contexts: message.contexts,
            attachments: message.attachments,
            hidden: message.hidden,
            ctx: message.ctx,
          };

          putRequest = objectStore.put(msg);
        }

        if (!putRequest) {
          return;
        }

        putRequest.onsuccess = () => {
          resolve();
        };

        putRequest.onerror = (putEvent) => {
          reject((putEvent.target as IDBRequest).error);
        };
      } else {
        reject(new Error(`Message with ID "${message.id}" not found in the database.`));
      }
    };

    getRequest.onerror = (event) => {
      reject((event.target as IDBRequest).error);
    };
  });
};
