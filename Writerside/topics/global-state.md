# Global State

The global state is a central store that manages the application's shared state. It provides a single source of truth for various application-wide settings and configurations.

## Properties

- `modalOpen` (boolean): Indicates whether a modal dialog is currently open
- `config` (Config): Application configuration object
- `modal` (Modal): Current modal dialog state
- `selectedAgent` (Record<string, AvailableAgent | undefined>): Currently selected AI agent

## Usage

The global state is typically accessed throughout the application to maintain consistent state across different components. It's particularly useful for:

- Managing modal dialogs
- Storing application configuration
- Tracking selected AI agents
- Sharing state between different parts of the application

## Example

```typescript
// Accessing global state
const isModalOpen = globalState.modalOpen;
const currentConfig = globalState.config;
const selectedAgent = globalState.selectedAgent;
``` 