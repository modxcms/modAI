# Executor Types

The executor types define the data structures and interfaces used throughout the executor module for handling AI service interactions and responses.

## Core Types

### UsageData
Tracks token usage for AI requests.

```typescript
type UsageData = {
  usage: {
    promptTokens: number;
    completionTokens: number;
  };
};
```

### ToolCalls
Represents AI tool execution requests.

```typescript
type ToolCalls = {
  id: string;
  name: string;
  arguments: string;
}[];
```

### Metadata
Contains model information for AI responses.

```typescript
type Metadata = {
  metadata?: {
    model: string;
  };
};
```

## Response Types

### TextData
Union type for text-based responses:
- `TextDataNoTools`: Simple text response
- `TextDataMaybeTools`: Text with optional tool calls
- `ToolsData`: Tool calls without text content

### ImageData
Represents image generation responses.

```typescript
type ImageData = Metadata & {
  __type: 'ImageData';
  id: string;
  url: string;
};
```

## Service Types

### ServiceHandler
Defines content and image handling functions.

```typescript
type ServiceHandler<CData, IData> = {
  content?: (data: CData) => TextData;
  image?: (data: IData) => ImageData;
};
```

### StreamHandler
Handles streaming response data.

```typescript
type StreamHandler = (
  chunk: string,
  buffer: string,
  currentData: TextData,
) => { buffer: string; currentData: TextData };
```

## Parameter Types

### ChatParams
Parameters for chat interactions.

```typescript
type ChatParams = {
  prompt: string;
  field?: string;
  agent?: string;
  contexts?: UserMessageContext[];
  attachments?: UserAttachment[];
  namespace?: string;
  messages: {
    role: 'user' | 'assistant' | 'tool';
    content?: string | ToolResponseContent;
    toolCalls?: ToolCalls;
    contexts?: UserMessageContext[];
    attachments?: UserAttachment[];
  }[];
};
```

### TextParams
Parameters for text processing.

```typescript
type TextParams = {
  field?: string;
  namespace?: string;
} & (
  | {
      resourceId: string | number;
    }
  | {
      content?: string;
    }
);
```

### VisionParams
Parameters for vision processing.

```typescript
type VisionParams = {
  field?: string;
  namespace?: string;
  image: string;
};
```

### ImageParams
Parameters for image generation.

```typescript
type ImageParams = {
  prompt: string;
  field?: string;
  namespace?: string;
};
```

### DownloadImageParams
Parameters for image downloading.

```typescript
type DownloadImageParams = {
  url: string;
  field?: string;
  namespace?: string;
  resource?: string | number;
  mediaSource?: string | number;
};
``` 