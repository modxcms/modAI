# Configuration and Initialization

The configuration system provides a way to set up and initialize the application with necessary settings and resources.

## AvailableAgent Type

Represents an available AI agent in the system.

### Properties
- `id` (string): Unique identifier for the agent
- `name` (string): Display name of the agent
- `contextProviders` (string[] | null): List of context providers supported by the agent

## Config Type

Defines the application configuration structure.

### Properties
- `name` (string, optional): Application name
- `assetsURL` (string): Base URL for application assets
- `apiURL` (string): Base URL for API endpoints
- `cssURL` (string): URL for application stylesheets
- `translateFn` (function, optional): Translation function for internationalization
- `availableAgents` (Record<string, AvailableAgent>): Map of available AI agents

## Initialization

The `init` function sets up the application with the provided configuration.

### Parameters
- `config` (Config): Application configuration object

### Returns
An object containing initialized modules:
- `chatHistory`: Chat history management
- `history`: History tracking
- `executor`: Command execution
- `ui`: User interface components
- `lng`: Language/translation utilities
- `initOnResource`: Resource initialization

## Example

```typescript
const config = {
  name: "My AI Application",
  assetsURL: "https://example.com/assets",
  apiURL: "https://api.example.com",
  cssURL: "https://example.com/styles.css",
  availableAgents: {
    "agent1": {
      id: "agent1",
      name: "Primary Agent",
      contextProviders: ["context1", "context2"]
    }
  }
};

const app = init(config);
``` 