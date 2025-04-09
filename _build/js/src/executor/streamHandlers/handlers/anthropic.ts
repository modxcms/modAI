import type { StreamHandler } from '../../types';

type StreamData =
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
    }
  | { type: 'message_start'; message: { id: string; usage: { input_tokens: number } } }
  | { type: 'message_delta'; usage: { output_tokens: number } };

export const anthropic: StreamHandler = (chunk, buffer, currentData, onChunkStream) => {
  buffer += chunk;
  let lastNewlineIndex = 0;
  let newlineIndex;

  while ((newlineIndex = buffer.indexOf('\n', lastNewlineIndex)) !== -1) {
    const line = buffer.slice(lastNewlineIndex, newlineIndex).trim();
    lastNewlineIndex = newlineIndex + 1;

    if (line.startsWith('data: ')) {
      const data = line.slice(6);

      try {
        const parsedData = JSON.parse(data) as StreamData;
        if (parsedData.type === 'message_start') {
          currentData.id = parsedData.message.id;
          currentData.usage = {
            promptTokens: parsedData.message.usage.input_tokens,
            completionTokens: 0,
          };
          continue;
        }

        if (parsedData.type === 'message_delta') {
          currentData.usage.completionTokens = parsedData.usage.output_tokens;
          continue;
        }

        if (
          parsedData.type !== 'content_block_delta' &&
          parsedData.type !== 'content_block_start'
        ) {
          continue;
        }

        let content = '';
        let toolCalls = currentData.toolCalls || undefined;

        if (
          parsedData.type === 'content_block_start' &&
          parsedData.content_block.type === 'tool_use'
        ) {
          if (!toolCalls) {
            toolCalls = [];
          }

          toolCalls[parsedData.index] = {
            id: parsedData.content_block.id,
            name: parsedData.content_block.name,
            arguments: '',
          };
        }
        if (parsedData.type === 'content_block_delta' && parsedData.delta.type === 'text_delta') {
          content = parsedData.delta.text || '';
        }

        if (
          parsedData.type === 'content_block_delta' &&
          parsedData.delta.type === 'input_json_delta'
        ) {
          if (!toolCalls) {
            toolCalls = [];
          }

          toolCalls[parsedData.index].arguments =
            toolCalls[parsedData.index].arguments + parsedData.delta.partial_json;
        }

        currentData = {
          __type: 'TextDataMaybeTools',
          id: currentData.id,
          content: (currentData.content ?? '') + content,
          toolCalls,
          usage: {
            completionTokens: currentData.usage.completionTokens || 0,
            promptTokens: currentData.usage.promptTokens || 0,
          },
        };

        if (onChunkStream && currentData.content) {
          onChunkStream(currentData);
        }
      } catch {
        /* empty */
      }
    }
  }

  return { buffer: buffer.slice(lastNewlineIndex), currentData };
};
