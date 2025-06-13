const DB_NAME = 'modAI';
const OBJECT_STORE_NAME = 'chats';
const DB_VERSION = 2;

let db: IDBDatabase | null = null;

type ChatRecord = {
  key: string;
  chat_id: number;
  user_id: number;
};

const initializeDatabase = async (): Promise<IDBDatabase> => {
  return new Promise((resolve, reject) => {
    if (db) {
      resolve(db);
      return;
    }

    const request = indexedDB.open(DB_NAME, DB_VERSION);

    request.onupgradeneeded = (event) => {
      const db = (event.target as IDBOpenDBRequest).result;

      if (db.objectStoreNames.contains('messages')) {
        db.deleteObjectStore('messages');
      }

      if (db.objectStoreNames.contains(OBJECT_STORE_NAME)) {
        db.deleteObjectStore(OBJECT_STORE_NAME);
      }

      const objectStore = db.createObjectStore(OBJECT_STORE_NAME, {
        keyPath: ['key', 'user_id'], // Composite key for key/user_id unique constraint
      });

      objectStore.createIndex('chat_id', 'chat_id', { unique: false });
      objectStore.createIndex('key', 'key', { unique: false });
      objectStore.createIndex('user_id', 'user_id', { unique: false });
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

export const getChatId = async (key: string, user_id: number): Promise<number | null> => {
  const db = await initializeDatabase();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(OBJECT_STORE_NAME, 'readonly');
    const objectStore = transaction.objectStore(OBJECT_STORE_NAME);
    const request = objectStore.get([key, user_id]);

    request.onsuccess = (event) => {
      const result = (event.target as IDBRequest<ChatRecord>).result;
      resolve(result ? result.chat_id : null);
    };

    request.onerror = (event) => {
      reject((event.target as IDBRequest<ChatRecord>).error);
    };
  });
};

export const createOrUpdateChat = async (
  key: string,
  chat_id: number,
  user_id: number,
): Promise<void> => {
  const db = await initializeDatabase();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(OBJECT_STORE_NAME, 'readwrite');
    const objectStore = transaction.objectStore(OBJECT_STORE_NAME);

    const chatRecord: ChatRecord = { key, chat_id, user_id };
    const request = objectStore.put(chatRecord);

    request.onsuccess = () => {
      resolve();
    };

    request.onerror = (event) => {
      const error = (event.target as IDBRequest).error;
      // Check if it's a constraint violation on chat_id
      if (error?.name === 'ConstraintError') {
        reject(new Error(`Chat ID "${chat_id}" already exists`));
      } else {
        reject(error);
      }
    };
  });
};

export const deleteChatByKeyAndUserId = async (key: string, user_id: number): Promise<boolean> => {
  const db = await initializeDatabase();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(OBJECT_STORE_NAME, 'readwrite');
    const objectStore = transaction.objectStore(OBJECT_STORE_NAME);
    const request = objectStore.delete([key, user_id]);

    request.onsuccess = () => {
      resolve(true);
    };

    request.onerror = (event) => {
      reject((event.target as IDBRequest).error);
    };
  });
};

export const deleteChatByChatId = async (chat_id: number): Promise<boolean> => {
  const db = await initializeDatabase();
  return new Promise((resolve, reject) => {
    const transaction = db.transaction(OBJECT_STORE_NAME, 'readwrite');
    const objectStore = transaction.objectStore(OBJECT_STORE_NAME);
    const index = objectStore.index('chat_id');

    let deletedCount = 0;
    let hasError = false;

    // Use openCursor to iterate through all records with matching chat_id
    const cursorRequest = index.openCursor(IDBKeyRange.only(chat_id));

    cursorRequest.onsuccess = (event) => {
      const cursor = (event.target as IDBRequest<IDBCursorWithValue>).result;

      if (cursor) {
        // Delete the current record
        const deleteRequest = objectStore.delete(cursor.primaryKey);

        deleteRequest.onsuccess = () => {
          deletedCount++;
          cursor.continue(); // Move to next matching record
        };

        deleteRequest.onerror = (deleteEvent) => {
          hasError = true;
          reject((deleteEvent.target as IDBRequest).error);
        };
      } else {
        // No more records to process
        if (!hasError) {
          resolve(deletedCount > 0); // Return true if at least one record was deleted
        }
      }
    };

    cursorRequest.onerror = (event) => {
      reject((event.target as IDBRequest).error);
    };
  });
};
