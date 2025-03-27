import { lng } from '../lng';

export type ServiceType = 'chatgpt' | 'claude' | 'gemini';
export type BufferMode = 'buffered' | 'stream';
export type ParserType = keyof ServiceHandlers[BufferMode][ServiceType];

export type ToolCalls = {
  id: string;
  name: string;
  arguments: string;
}[];

export type TextDataMaybeTools = {
  __type: 'TextDataMaybeTools';
  id: string;
  content: string;
  toolCalls?: ToolCalls;
};

export type ToolsData = {
  __type: 'ToolsData';
  id: string;
  content?: undefined;
  toolCalls: ToolCalls;
};

export type TextDataNoTools = {
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

export type ChatGPTCompletionsData = {
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
};

export type ChatGPTStreamCompletionsData = {
  id: string;
  choices?: {
    delta?: {
      content?: string | null;
      tool_calls?: {
        index: number;
        id: string;
        type: string;
        function: {
          name: string;
          arguments: string;
        };
      }[];
    };
  }[];
};

export type ChatGPTImageData = {
  data?: {
    url?: string;
    b64_json?: string;
  }[];
};

export type ClaudeCompletionsData = {
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
};

export type ClaudeStreamCompletionsData =
  | {
      id: string;
      type: 'content_block_delta';
      index: number;
      delta:
        | {
            type: 'text_delta';
            text: string;
          }
        | {
            type: 'input_json_delta';
            partial_json: string;
          };
    }
  | {
      type: 'content_block_start';
      index: number;
      content_block:
        | {
            type: 'text';
            text: string;
          }
        | {
            type: 'tool_use';
            id: string;
            name: string;
            input: Record<string, unknown>;
          };
    };

export type GeminiCompletionsData = {
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
};

export type GeminiStreamCompletionsData = {
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
};

export type GeminiImageData = {
  predictions?: {
    bytesBase64Encoded?: string;
  }[];
};

export type ServiceHandlers = {
  buffered: {
    chatgpt: {
      content: (data: ChatGPTCompletionsData) => TextData;
      image: (data: ChatGPTImageData) => ImageData;
    };
    claude: {
      content: (data: ClaudeCompletionsData) => TextData;
    };
    gemini: {
      content: (data: GeminiCompletionsData) => TextData;
      image: (data: GeminiImageData) => ImageData;
    };
  };
  stream: {
    chatgpt: {
      content: (newData: ChatGPTStreamCompletionsData, currentData: TextData) => TextData;
    };
    claude: {
      content: (newData: ClaudeStreamCompletionsData, currentData: TextData) => TextData;
    };
    gemini: {
      content: (newData: GeminiStreamCompletionsData, currentData: TextData) => TextData;
    };
  };
};

export const validateServiceParser = (
  service: string | undefined,
  parser: string | undefined,
  isStream: boolean,
): { service: ServiceType; parser: ParserType; mode: BufferMode } => {
  if (!service || !parser) {
    throw new Error(lng('modai.error.service_required'));
  }

  const mode = isStream ? 'stream' : 'buffered';
  const serviceType = service as ServiceType;
  const parserType = parser as ParserType;

  if (!services[mode]?.[serviceType]?.[parserType]) {
    throw new Error(lng('modai.error.service_unsupported'));
  }

  return { service: serviceType, parser: parserType, mode };
};

export const services: ServiceHandlers = {
  buffered: {
    chatgpt: {
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
    claude: {
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
          };
        }

        if (!content) {
          return {
            __type: 'ToolsData',
            id,
            toolCalls: tools,
          };
        }

        return {
          __type: 'TextDataMaybeTools',
          id,
          toolCalls: tools,
          content: content,
        };
      },
    },
    gemini: {
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
          };
        }

        if (!content) {
          return {
            __type: 'ToolsData',
            id: crypto.randomUUID(),
            toolCalls: tools,
          };
        }

        return {
          __type: 'TextDataMaybeTools',
          id: crypto.randomUUID(),
          content: content,
          toolCalls: tools,
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
  },
  stream: {
    chatgpt: {
      content: (newData, currentData) => {
        if (!newData?.choices?.[0]?.delta?.tool_calls && !newData?.choices?.[0]?.delta?.content) {
          return currentData;
        }

        let content = '';
        let toolCalls = currentData.toolCalls || undefined;

        if (newData?.choices?.[0]?.delta?.tool_calls?.[0]) {
          if (!toolCalls) {
            toolCalls = [];
          }

          const toolCall = newData.choices[0].delta.tool_calls[0];

          if (!toolCalls[toolCall.index]) {
            toolCalls[toolCall.index] = {
              id: '',
              name: '',
              arguments: '',
            };
          }

          if (toolCall.id) {
            toolCalls[toolCall.index].id = toolCall.id;
          }

          if (toolCall.function.name) {
            toolCalls[toolCall.index].name = toolCall.function.name;
          }

          if (toolCall.function.arguments) {
            toolCalls[toolCall.index].arguments += toolCall.function.arguments;
          }
        }

        if (newData?.choices?.[0]?.delta?.content) {
          content = newData?.choices?.[0]?.delta?.content;
        }

        return {
          __type: 'TextDataMaybeTools',
          id: currentData.id,
          content: (currentData.content ?? '') + content,
          toolCalls,
        };
      },
    },
    claude: {
      content: (newData, currentData) => {
        let content = '';
        let toolCalls = currentData.toolCalls || undefined;

        if (newData.type === 'content_block_start' && newData.content_block.type === 'tool_use') {
          if (!toolCalls) {
            toolCalls = [];
          }

          toolCalls[newData.index] = {
            id: newData.content_block.id,
            name: newData.content_block.name,
            arguments: '',
          };
        }
        if (newData.type === 'content_block_delta' && newData.delta.type === 'text_delta') {
          content = newData.delta.text || '';
        }

        if (newData.type === 'content_block_delta' && newData.delta.type === 'input_json_delta') {
          if (!toolCalls) {
            toolCalls = [];
          }

          toolCalls[newData.index].arguments =
            toolCalls[newData.index].arguments + newData.delta.partial_json;
        }

        return {
          __type: 'TextDataMaybeTools',
          id: currentData.id,
          content: (currentData.content ?? '') + content,
          toolCalls,
        };
      },
    },
    gemini: {
      content: (newData, currentData) => {
        let content = '';
        let toolCalls = currentData.toolCalls || undefined;

        if (newData?.candidates?.[0]?.content?.parts) {
          for (const part of newData.candidates[0].content.parts) {
            if (part.text) {
              content += part.text;
              continue;
            }

            if (part.functionCall) {
              if (!toolCalls) {
                toolCalls = [];
              }

              toolCalls.push({
                id: crypto.randomUUID(),
                name: part.functionCall.name,
                arguments: JSON.stringify(part.functionCall.args),
              });
            }
          }
        }

        return {
          __type: 'TextDataMaybeTools',
          id: currentData.id,
          content: (currentData.content || '') + content,
          toolCalls,
        };
      },
    },
  },
};
