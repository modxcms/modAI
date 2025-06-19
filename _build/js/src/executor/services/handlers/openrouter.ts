import { lng } from '../../../lng';

import type { ServiceHandler } from '../../types';

type CompletionsData = {
  id: string;
  choices?: {
    message?: {
      content?: string | null;
      tool_calls?: {
        id: string;
        type: string;
        function: {
          name: string;
          arguments: string;
        };
      }[];
    };
  }[];
  usage: {
    prompt_tokens: number;
    completion_tokens: number;
  };
};

type ImageData = {
  data?: {
    url?: string;
    b64_json?: string;
  }[];
};

export const openrouter: ServiceHandler<CompletionsData, ImageData> = {
  content: (data) => {
    const content = data?.choices?.[0]?.message?.content;
    const tools = data?.choices?.[0]?.message?.tool_calls;

    if (!content && !tools) {
      throw new Error(lng('modai.error.failed_request'));
    }

    const id = data.id;

    if (!tools) {
      return {
        __type: 'TextDataNoTools',
        id,
        content: content as string,
        usage: {
          completionTokens: data?.usage.completion_tokens,
          promptTokens: data?.usage.prompt_tokens,
        },
      };
    }

    if (!content) {
      return {
        __type: 'ToolsData',
        id,
        toolCalls: tools.map((tool) => ({
          id: tool.id,
          name: tool.function.name,
          arguments: tool.function.arguments,
        })),
        usage: {
          completionTokens: data?.usage.completion_tokens,
          promptTokens: data?.usage.prompt_tokens,
        },
      };
    }

    return {
      __type: 'TextDataMaybeTools',
      id,
      content: content,
      toolCalls: tools.map((tool) => ({
        id: tool.id,
        name: tool.function.name,
        arguments: tool.function.arguments,
      })),
      usage: {
        completionTokens: data?.usage.completion_tokens,
        promptTokens: data?.usage.prompt_tokens,
      },
    };
  },
  image: (data) => {
    let url = data?.data?.[0]?.url;

    if (!url) {
      url = data?.data?.[0]?.b64_json;

      if (!url) {
        throw new Error(lng('modai.error.failed_request'));
      }

      url = `data:image/png;base64,${url}`;
    }

    return {
      __type: 'ImageData',
      id: window.crypto.randomUUID(),
      url,
    };
  },
};
