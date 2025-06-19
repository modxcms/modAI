import { lng } from '../../../lng';
import { OutputItem } from '../../types/openai';

import type { ServiceHandler } from '../../types';

type CompletionsData = {
  id: string;
  output: OutputItem[];
  usage: {
    input_tokens: number;
    output_tokens: number;
    total_tokens: number;
  };
};

type ImageData = {
  data?: {
    url?: string;
    b64_json?: string;
  }[];
};

export const openai: ServiceHandler<CompletionsData, ImageData> = {
  content: (data) => {
    const content = data?.output.filter((item) => item.type === 'message');
    const tools = data?.output.filter((item) => item.type === 'function_call');

    if (!content && !tools) {
      throw new Error(lng('modai.error.failed_request'));
    }

    const id = data.id;

    if (!tools || tools.length === 0) {
      return {
        __type: 'TextDataNoTools',
        id,
        content: content[0].content[0].text,
        usage: {
          completionTokens: data?.usage.output_tokens,
          promptTokens: data?.usage.input_tokens,
        },
      };
    }

    if (!content || content.length === 0) {
      return {
        __type: 'ToolsData',
        id,
        toolCalls: tools.map((tool) => ({
          id: tool.id,
          name: tool.name,
          arguments: tool.arguments,
        })),
        usage: {
          completionTokens: data?.usage.output_tokens,
          promptTokens: data?.usage.input_tokens,
        },
      };
    }

    return {
      __type: 'TextDataMaybeTools',
      id,
      content: content[0].content[0].text,
      toolCalls: tools.map((tool) => ({
        id: tool.id,
        name: tool.name,
        arguments: tool.arguments,
      })),
      usage: {
        completionTokens: data?.usage.output_tokens,
        promptTokens: data?.usage.input_tokens,
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
