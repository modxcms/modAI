import { Annotation, OutputItem } from '../../types/openai';

import type { StreamHandler } from '../../types';

type StreamData =
  | {
      type: 'response.created';
    }
  | {
      type: 'response.in_progress';
    }
  | {
      type: 'response.output_item.added';
    }
  | {
      type: 'response.content_part.added';
    }
  | {
      type: 'response.output_text.delta';
      item_id: string;
      delta: string;
    }
  | {
      type: 'response.output_text.done';
    }
  | {
      type: 'response.content_part.done';
    }
  | {
      type: 'response.output_item.done';
    }
  | {
      type: 'response.image_generation_call.in_progress';
    }
  | {
      type: 'response.image_generation_call.generating';
    }
  | {
      type: 'response.image_generation_call.partial_image';
    }
  | {
      type: 'response.image_generation_call.completed';
    }
  | {
      type: 'response.output_text.annotation.added';
      item_id: string;
      annotation: Annotation;
    }
  | {
      type: 'response.completed';
      response: {
        output: OutputItem[];
        usage: {
          input_tokens: number;
          output_tokens: number;
          total_tokens: number;
        };
      };
    };

export const openai: StreamHandler = (chunk, buffer, currentData) => {
  buffer += chunk;
  let lastNewlineIndex = 0;
  let newlineIndex;

  while ((newlineIndex = buffer.indexOf('\n\n', lastNewlineIndex)) !== -1) {
    const line = buffer.slice(lastNewlineIndex, newlineIndex).trim();
    lastNewlineIndex = newlineIndex + 1;
    const rawData = line.split('\n');

    let data = null;

    if (
      rawData.length >= 2 &&
      rawData[0].startsWith('event: ') &&
      rawData[1].startsWith('data: ')
    ) {
      try {
        data = JSON.parse(rawData[1].slice(6)) as StreamData;
      } catch {
        continue;
      }
    } else {
      continue;
    }

    if (
      !data ||
      (data.type !== 'response.completed' && data.type !== 'response.output_text.delta')
    ) {
      continue;
    }

    try {
      let content = '';
      if (data.type === 'response.output_text.delta') {
        content = data.delta;
        currentData.id = data.item_id;
      }

      if (data.type === 'response.completed') {
        currentData.usage = {
          completionTokens: data.response.usage.output_tokens || 0,
          promptTokens: data.response.usage.input_tokens || 0,
        };

        let toolCalls = currentData.toolCalls || undefined;
        data.response.output.forEach((item) => {
          if (item.type === 'function_call') {
            if (!toolCalls) {
              toolCalls = [];
            }

            toolCalls.push({
              id: item.call_id,
              name: item.name,
              arguments: item.arguments,
            });
          }
        });
        currentData.toolCalls = toolCalls;
      }

      currentData = {
        __type: 'TextDataMaybeTools',
        id: currentData.id,
        content: (currentData.content ?? '') + content,
        toolCalls: currentData.toolCalls,
        usage: {
          completionTokens: currentData.usage.completionTokens ?? 0,
          promptTokens: currentData.usage.promptTokens ?? 0,
        },
      };
    } catch {
      /* empty */
    }
  }

  return { buffer: buffer.slice(lastNewlineIndex), currentData };
};
