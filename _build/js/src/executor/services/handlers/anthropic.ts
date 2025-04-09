import { lng } from '../../../lng';

import type { ServiceHandler, TextData } from '../../types';

type CompletionsData = {
  id: string;
  content?: (
    | {
        type: 'text';
        text?: string;
      }
    | {
        type: 'tool_use';
        id: string;
        name: string;
        input: Record<string, unknown>;
      }
  )[];
  usage: {
    input_tokens: number;
    output_tokens: number;
  };
};

export const anthropic: ServiceHandler<CompletionsData, ImageData> = {
  content: (data) => {
    let content: string | undefined;
    const tools: TextData['toolCalls'] = [];
    data?.content?.map((contentItem) => {
      if (contentItem.type === 'text') {
        content = contentItem.text;
      }

      if (contentItem.type === 'tool_use') {
        tools.push({
          id: contentItem.id,
          name: contentItem.name,
          arguments: JSON.stringify(contentItem.input),
        });
      }
    });

    if (!content && tools.length === 0) {
      throw new Error(lng('modai.error.failed_request'));
    }

    const id = data.id;

    if (tools.length === 0) {
      return {
        __type: 'TextDataNoTools',
        id,
        content: content as string,
        usage: {
          completionTokens: data?.usage.output_tokens,
          promptTokens: data?.usage.input_tokens,
        },
      };
    }

    if (!content) {
      return {
        __type: 'ToolsData',
        id,
        toolCalls: tools,
        usage: {
          completionTokens: data?.usage.output_tokens,
          promptTokens: data?.usage.input_tokens,
        },
      };
    }

    return {
      __type: 'TextDataMaybeTools',
      id,
      toolCalls: tools,
      content: content,
      usage: {
        completionTokens: data?.usage.output_tokens,
        promptTokens: data?.usage.input_tokens,
      },
    };
  },
};
