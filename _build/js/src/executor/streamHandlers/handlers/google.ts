import type { StreamHandler } from '../../types';

type StreamData = {
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

export const google: StreamHandler = (chunk, buffer, currentData) => {
  const jsonLines = chunk
    .trim()
    .split(',\r\n')
    .map((line) => line.replace(/^\[|]$/g, ''))
    .filter((line) => line.trim() !== '');

  for (const line of jsonLines) {
    try {
      const parsedData = JSON.parse(line) as StreamData;

      let content = '';
      let toolCalls = currentData.toolCalls || undefined;

      if (parsedData?.candidates?.[0]?.content?.parts) {
        for (const part of parsedData.candidates[0].content.parts) {
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

      currentData = {
        __type: 'TextDataMaybeTools',
        id: currentData.id,
        content: (currentData.content || '') + content,
        toolCalls,
        usage: {
          completionTokens: parsedData?.usageMetadata.candidatesTokenCount,
          promptTokens: parsedData?.usageMetadata.promptTokenCount,
        },
      };
    } catch {
      /* empty */
    }
  }

  return { buffer, currentData };
};
