import { aiFetch, modxFetch } from './apiClient';

import type { TextData, ImageData, ToolCalls, TextDataNoTools } from './services';
import type {
  ChunkStream,
  DownloadImageParams,
  FreeTextParams,
  ImageParams,
  TextParams,
  ToolResponseContent,
  VisionParams,
} from './types';

export const executor = {
  mgr: {
    download: {
      image: async (params: DownloadImageParams) => {
        return await modxFetch<{ url: string; fullUrl: string }>('Download\\Image', params);
      },
    },
    tools: {
      run: async (toolCalls: ToolCalls, controller?: AbortController) => {
        return await modxFetch<{ id: string; content: ToolResponseContent }>(
          'Tools\\Run',
          { toolCalls },
          controller,
        );
      },
    },
    prompt: {
      freeText: async (
        params: FreeTextParams,
        onChunkStream?: ChunkStream<TextData>,
        controller?: AbortController,
      ) => {
        return aiFetch<TextData>('Prompt\\FreeText', params, onChunkStream, controller);
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
