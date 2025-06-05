import type { StreamHandler } from '../../types';

type StreamData = {
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
  usage: null | {
    prompt_tokens: number;
    completion_tokens: number;
  };
};

export const legacyOpenai: StreamHandler = (chunk, buffer, currentData) => {
  buffer += chunk;
  let lastNewlineIndex = 0;
  let newlineIndex;

  while ((newlineIndex = buffer.indexOf('\n', lastNewlineIndex)) !== -1) {
    const line = buffer.slice(lastNewlineIndex, newlineIndex).trim();
    lastNewlineIndex = newlineIndex + 1;

    if (line.startsWith('data: ')) {
      const data = line.slice(6);

      if (data === '[DONE]') {
        continue;
      }

      try {
        const parsedData = JSON.parse(data) as StreamData;

        if (parsedData?.usage) {
          currentData.usage = {
            completionTokens: parsedData?.usage?.completion_tokens || 0,
            promptTokens: parsedData?.usage?.prompt_tokens || 0,
          };
        }

        if (
          !parsedData?.choices?.[0]?.delta?.tool_calls &&
          !parsedData?.choices?.[0]?.delta?.content
        ) {
          continue;
        }

        let content = '';
        let toolCalls = currentData.toolCalls || undefined;

        if (parsedData?.choices?.[0]?.delta?.tool_calls?.[0]) {
          if (!toolCalls) {
            toolCalls = [];
          }

          const toolCall = parsedData.choices[0].delta.tool_calls[0];

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

        if (parsedData?.choices?.[0]?.delta?.content) {
          content = parsedData?.choices?.[0]?.delta?.content;
        }

        currentData = {
          __type: 'TextDataMaybeTools',
          id: parsedData.id,
          content: (currentData.content ?? '') + content,
          toolCalls,
          usage: {
            completionTokens: currentData.usage.completionTokens ?? 0,
            promptTokens: currentData.usage.promptTokens ?? 0,
          },
        };
      } catch {
        /* empty */
      }
    }
  }

  return { buffer: buffer.slice(lastNewlineIndex), currentData };
};
