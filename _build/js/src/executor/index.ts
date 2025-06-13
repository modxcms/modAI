import { aiFetch, modxFetch } from './apiClient';
import { debounce } from '../ui/utils';

import type { Message } from '../chatHistory/types';
import type { Chat } from '../chats';
import type {
  TextData,
  ImageData,
  ToolCalls,
  TextDataNoTools,
  ChunkStream,
  DownloadImageParams,
  ChatParams,
  ImageParams,
  TextParams,
  ToolResponseContent,
  VisionParams,
  UsageData,
} from './types';

export const executor = {
  download: {
    image: async (params: DownloadImageParams) => {
      return await modxFetch<{ url: string; fullUrl: string }>('Download\\Image', params);
    },
  },
  tools: {
    run: async (
      params: { toolCalls: ToolCalls; agent?: string; chatId?: number },
      controller?: AbortController,
    ) => {
      return await modxFetch<{ id: string; content: ToolResponseContent }>(
        'Tools\\Run',
        params,
        controller,
      );
    },
  },
  context: {
    get: async (params: { prompt: string; agent: string }, controller?: AbortController) => {
      return await modxFetch<{ contexts: string[] }>('Context\\Get', params, controller);
    },
  },
  chat: {
    storeMessage: async (
      chatId: number,
      msg: Message,
      usage?: UsageData['usage'],
      controller?: AbortController,
    ) => {
      await modxFetch('Chat\\StoreMessage', { chatId, msg, usage }, controller);
    },
    loadMessages: async (chatId: number, controller?: AbortController) => {
      return await modxFetch<{ messages: Message[]; chat: { title: string; view_only: boolean } }>(
        'Chat\\LoadMessages',
        { chatId },
        controller,
      );
    },
    loadChats: async (controller?: AbortController) => {
      return await modxFetch<{
        chats: Chat[];
      }>('Chat\\LoadChats', {}, controller);
    },
    setChatTitle: async (chatId: number, title: string, controller?: AbortController) => {
      await modxFetch('Chat\\SetChatTitle', { chatId, title }, controller);
    },
    pinChat: async (chatId: number, pinned: boolean, controller?: AbortController) => {
      await modxFetch('Chat\\PinChat', { chatId, pinned }, controller);
    },
    setPublicChat: async (chatId: number, publicStatus: boolean, controller?: AbortController) => {
      await modxFetch('Chat\\PublicChat', { chatId, publicStatus }, controller);
    },
    deleteChat: async (chatId: number, controller?: AbortController) => {
      await modxFetch('Chat\\DeleteChat', { chatId }, controller);
    },
    cloneChat: async (chatId: number, controller?: AbortController) => {
      await modxFetch('Chat\\CloneChat', { chatId }, controller);
    },
    searchChats: debounce(async (query: string, controller?: AbortController) => {
      return await modxFetch<{ chats: Record<number, number> }>(
        'Chat\\SearchChat',
        { query },
        controller,
      );
    }, 300),
    deleteMessages: async (
      params: { chatId: number; fromMessageId: string },
      controller?: AbortController,
    ) => {
      await modxFetch('Chat\\DeleteMessages', params, controller);
    },
  },
  prompt: {
    chat: async (
      params: ChatParams,
      onChunkStream?: ChunkStream<TextData>,
      controller?: AbortController,
    ) => {
      return aiFetch<TextData>('Prompt\\Chat', params, onChunkStream, controller);
    },
    chatTitle: async (params: { message: string }, controller?: AbortController) => {
      return aiFetch<TextDataNoTools>('Prompt\\ChatTitle', params, undefined, controller);
    },
    text: async (
      params: TextParams,
      onChunkStream?: ChunkStream<TextDataNoTools>,
      controller?: AbortController,
    ) => {
      return aiFetch<TextDataNoTools>('Prompt\\Text', params, onChunkStream, controller);
    },
    vision: async (
      params: VisionParams,
      onChunkStream?: ChunkStream<TextDataNoTools>,
      controller?: AbortController,
    ) => {
      return aiFetch<TextDataNoTools>('Prompt\\Vision', params, onChunkStream, controller);
    },
    image: async (params: ImageParams, controller?: AbortController) => {
      return aiFetch<ImageData>('Prompt\\Image', params, undefined, controller);
    },
  },

  /**
   * @deprecated drop the mgr namespace
   */
  mgr: {
    download: {
      image: async (params: DownloadImageParams) => {
        return await modxFetch<{ url: string; fullUrl: string }>('Download\\Image', params);
      },
    },
    tools: {
      run: async (
        params: { toolCalls: ToolCalls; agent?: string },
        controller?: AbortController,
      ) => {
        return await modxFetch<{ id: string; content: ToolResponseContent }>(
          'Tools\\Run',
          params,
          controller,
        );
      },
    },
    context: {
      get: async (params: { prompt: string; agent: string }, controller?: AbortController) => {
        return await modxFetch<{ contexts: string[] }>('Context\\Get', params, controller);
      },
    },
    prompt: {
      freeText: async (
        params: ChatParams,
        onChunkStream?: ChunkStream<TextData>,
        controller?: AbortController,
      ) => {
        return aiFetch<TextData>('Prompt\\Chat', params, onChunkStream, controller);
      },
      chat: async (
        params: ChatParams,
        onChunkStream?: ChunkStream<TextData>,
        controller?: AbortController,
      ) => {
        return aiFetch<TextData>('Prompt\\Chat', params, onChunkStream, controller);
      },
      text: async (
        params: TextParams,
        onChunkStream?: ChunkStream<TextDataNoTools>,
        controller?: AbortController,
      ) => {
        return aiFetch<TextDataNoTools>('Prompt\\Text', params, onChunkStream, controller);
      },
      vision: async (
        params: VisionParams,
        onChunkStream?: ChunkStream<TextDataNoTools>,
        controller?: AbortController,
      ) => {
        return aiFetch<TextDataNoTools>('Prompt\\Vision', params, onChunkStream, controller);
      },
      image: async (params: ImageParams, controller?: AbortController) => {
        return aiFetch<ImageData>('Prompt\\Image', params, undefined, controller);
      },
    },
  },
};
