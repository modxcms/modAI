# Service Executor

The service executor handles the execution of AI services, supporting both standard and streaming responses.

## Functions

### serviceExecutor
Main function for executing AI services.

```typescript
async function serviceExecutor<D extends ServiceResponse>(
  details: ExecutorData,
  onChunkStream?: ChunkStream<D>,
  controller?: AbortController,
): Promise<D>
```

#### Parameters
- `details` (ExecutorData): Execution details
- `onChunkStream` (ChunkStream<D>, optional): Callback for streaming data
- `controller` (AbortController, optional): Controller for request cancellation

#### Returns
- `Promise<D>`: Response data of type D

### callService
Handles standard service calls.

```typescript
async function callService(
  details: ForExecutor,
  signal?: AbortSignal
): Promise<any>
```

### callStreamService
Handles streaming service calls.

```typescript
async function callStreamService<D extends ServiceResponse>(
  executorDetails: ForExecutor,
  onChunkStream?: ChunkStream<D>,
  signal?: AbortSignal,
): Promise<D>
```

## Error Handling

### errorHandler
Centralized error handling for service responses.

```typescript
async function errorHandler(res: Response): Promise<void>
```

## Features

- Support for both standard and streaming responses
- Automatic error handling
- Request cancellation
- Type-safe responses
- Service-specific parsers
- Stream handlers

## Usage Example

```typescript
// Standard service call
const result = await serviceExecutor({
  forExecutor: {
    url: 'https://api.example.com/service',
    body: JSON.stringify({ prompt: 'Hello' }),
    service: 'chat',
    model: 'gpt-4',
    headers: { 'Content-Type': 'application/json' },
    parser: 'json',
    stream: false
  }
});

// Streaming service call
const result = await serviceExecutor({
  forExecutor: {
    url: 'https://api.example.com/service',
    body: JSON.stringify({ prompt: 'Hello' }),
    service: 'chat',
    model: 'gpt-4',
    headers: { 'Content-Type': 'application/json' },
    parser: 'json',
    stream: true
  }
}, (chunk) => {
  // Handle streaming data
  console.log('Received chunk:', chunk);
});
``` 