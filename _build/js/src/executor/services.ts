import { lng } from '../lng';

export type ServiceType = keyof ServiceHandlers;
export type ParserType = keyof ServiceHandlers[ServiceType];

export type UsageData = {
  usage: {
    promptTokens: number;
    completionTokens: number;
  };
};

export type ToolCalls = {
  id: string;
  name: string;
  arguments: string;
}[];

export type TextDataMaybeTools = UsageData & {
  __type: 'TextDataMaybeTools';
  id: string;
  content: string;
  toolCalls?: ToolCalls;
};

export type ToolsData = UsageData & {
  __type: 'ToolsData';
  id: string;
  content?: undefined;
  toolCalls: ToolCalls;
};

export type TextDataNoTools = UsageData & {
  __type: 'TextDataNoTools';
  id: string;
  content: string;
  toolCalls?: undefined;
};

export type TextData = TextDataNoTools | TextDataMaybeTools | ToolsData;

export type ImageData = {
  __type: 'ImageData';
  id: string;
  url: string;
};

export type OpenAICompletionsData = {
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

export type OpenAIImageData = {
  data?: {
    url?: string;
    b64_json?: string;
  }[];
};

export type AnthropicCompletionsData = {
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

export type GoogleCompletionsData = {
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

export type GoogleImageData = {
  predictions?: {
    bytesBase64Encoded?: string;
  }[];
};

export type ServiceHandlers = {
  openai: {
    content: (data: OpenAICompletionsData) => TextData;
    image: (data: OpenAIImageData) => ImageData;
  };
  anthropic: {
    content: (data: AnthropicCompletionsData) => TextData;
  };
  google: {
    content: (data: GoogleCompletionsData) => TextData;
    image: (data: GoogleImageData) => ImageData;
  };
};

export const validateServiceParser = (
  service: string | undefined,
  parser: string | undefined,
): { service: ServiceType; parser: ParserType } => {
  if (!service || !parser) {
    throw new Error(lng('modai.error.service_required'));
  }

  const serviceType = service as ServiceType;
  const parserType = parser as ParserType;

  if (!services[serviceType]?.[parserType]) {
    throw new Error(lng('modai.error.service_unsupported'));
  }

  return { service: serviceType, parser: parserType };
};

export const services: ServiceHandlers = {
  openai: {
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
        id: crypto.randomUUID(),
        url,
      };
    },
  },
  anthropic: {
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
  },
  google: {
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
  },
};
