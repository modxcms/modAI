# API Client

The API client provides functions for making HTTP requests to the backend services, with support for both standard and streaming responses.

## Functions

### modxFetch
Makes a standard HTTP request to the MODX backend.

```typescript
async function modxFetch<R>(
  action: string,
  params: Record<string, unknown>,
  controller?: AbortController,
): Promise<R>
```

#### Parameters
- `action` (string): API action to call
- `params` (Record<string, unknown>): Request parameters
- `controller` (AbortController, optional): Controller for request cancellation

#### Returns
- `Promise<R>`: Response data of type R

### aiFetch
Makes an HTTP request with support for streaming responses.

```typescript
async function aiFetch<D extends ServiceResponse>(
  action: string,
  params: Record<string, unknown>,
  onChunkStream?: ChunkStream<D>,
  controller?: AbortController,
): Promise<D>
```

#### Parameters
- `action` (string): API action to call
- `params` (Record<string, unknown>): Request parameters
- `onChunkStream` (ChunkStream<D>, optional): Callback for streaming data
- `controller` (AbortController, optional): Controller for request cancellation

#### Returns
- `Promise<D>`: Response data of type D

## Features

- Automatic error handling
- Request cancellation support
- Streaming response handling
- Type-safe responses
- JSON request/response handling
- Global configuration integration

## Usage Example

```typescript
// Standard request
const result = await modxFetch<MyResponseType>('myAction', {
  param1: 'value1',
  param2: 'value2'
});

// Streaming request
const result = await aiFetch<TextData>('chat', {
  prompt: 'Hello',
  messages: []
}, (chunk) => {
  // Handle streaming data
  console.log('Received chunk:', chunk);
});
```

## Error Handling

The API client automatically handles errors and throws them with appropriate messages:
- API errors are extracted from the response
- Network errors are propagated
- Invalid responses are caught and reported 