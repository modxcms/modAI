# Chat History

The chat history system manages the conversation flow between users and AI agents, supporting various message types and features.

## Message Types

### BaseMessage
Common properties for all message types:
- `id` (string): Unique message identifier
- `hidden` (boolean): Visibility state
- `ctx` (Record<string, unknown>): Context data

### UserMessage
Represents a message from the user.

#### Properties
- `__type`: "UserMessage"
- `role`: "user"
- `content` (string): Message text
- `contexts` (UserMessageContext[]): Additional context data
- `attachments` (UserAttachment[]): Attached files or media
- `el` (UpdatableHTMLElement): Associated DOM element

### AssistantMessage
Represents a message from the AI assistant.

#### Properties
- `__type`: "AssistantMessage"
- `role`: "assistant"
- `content` (string | undefined): Message text
- `contentType` (AssistantMessageContentType): Type of content ("text" | "image")
- `toolCalls` (ToolCalls): Tool execution requests
- `el` (UpdatableHTMLElement): Associated DOM element

### ToolResponseMessage
Represents a response from a tool execution.

#### Properties
- `__type`: "ToolResponseMessage"
- `role`: "tool"
- `content` (ToolResponseContent): Tool response data
- `el` (UpdatableHTMLElement): Associated DOM element

## ChatHistory Interface

Provides methods for managing the conversation:

### Methods
- `addUserMessage`: Add a new user message
- `addAssistantMessage`: Add a new assistant message
- `addToolCallsMessage`: Add a tool calls message
- `addToolResponseMessage`: Add a tool response message
- `updateAssistantMessage`: Update an existing assistant message
- `getAssistantMessage`: Retrieve a specific assistant message
- `getMessages`: Get all messages
- `getMessagesHistory`: Get message history in a simplified format
- `clearHistory`: Clear all messages
- `clearHistoryFrom`: Clear messages from a specific point

## Example

```typescript
// Adding a user message
chatHistory.addUserMessage("Hello, how can you help me?");

// Adding an assistant message
chatHistory.addAssistantMessage({
  content: "I can help you with various tasks.",
  contentType: "text"
});

// Getting message history
const history = chatHistory.getMessagesHistory();
``` 