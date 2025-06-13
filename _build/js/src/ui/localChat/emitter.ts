import emitron from 'emitron';

type Events = {
  'chat:new': { public: boolean };
  'chat:delete': { chatId: number };
  loading: { isLoading: boolean; isPreloading: boolean; hasMessages: boolean };
};

export const emitter = emitron<Events>();
