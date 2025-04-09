import { lng } from '../../../lng';

import type { ServiceHandler } from '../../types';

type CompletionsData = {
  candidates?: {
    content?: {
      parts?: {
        text?: string;
        functionCall?: {
          name: string;
          args: Record<string, unknown>;
        };
      }[];
    };
  }[];
  usageMetadata: {
    promptTokenCount: number;
    candidatesTokenCount: number;
  };
};

type ImageData = {
  predictions?: {
    bytesBase64Encoded?: string;
  }[];
};

export const google: ServiceHandler<CompletionsData, ImageData> = {
  content: (data) => {
    let content = '';
    let tools = undefined;

    if (!data?.candidates?.[0]?.content?.parts) {
      throw new Error(lng('modai.error.failed_request'));
    }

    for (const part of data.candidates[0].content.parts) {
      if (part.text) {
        content += part.text;
        continue;
      }

      if (part.functionCall) {
        if (!tools) {
          tools = [];
        }

        tools.push({
          id: crypto.randomUUID(),
          name: part.functionCall.name,
          arguments: JSON.stringify(part.functionCall.args),
        });
      }
    }

    if (!content && !tools) {
      throw new Error(lng('modai.error.failed_request'));
    }

    if (!tools) {
      return {
        __type: 'TextDataNoTools',
        id: crypto.randomUUID(),
        content: content as string,
        usage: {
          completionTokens: data?.usageMetadata.candidatesTokenCount,
          promptTokens: data?.usageMetadata.promptTokenCount,
        },
      };
    }

    if (!content) {
      return {
        __type: 'ToolsData',
        id: crypto.randomUUID(),
        toolCalls: tools,
        usage: {
          completionTokens: data?.usageMetadata.candidatesTokenCount,
          promptTokens: data?.usageMetadata.promptTokenCount,
        },
      };
    }

    return {
      __type: 'TextDataMaybeTools',
      id: crypto.randomUUID(),
      content: content,
      toolCalls: tools,
      usage: {
        completionTokens: data?.usageMetadata.candidatesTokenCount,
        promptTokens: data?.usageMetadata.promptTokenCount,
      },
    };
  },
  image: (data) => {
    const base64 = data?.predictions?.[0]?.bytesBase64Encoded;

    if (!base64) {
      throw new Error(lng('modai.error.failed_request'));
    }

    return {
      __type: 'ImageData',
      id: crypto.randomUUID(),
      url: `data:image/png;base64,${base64}`,
    };
  },
};
