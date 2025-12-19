# Context Providers

## What a Context Provider Is
A **Context Provider** is a component that knows how to:
- Retrieve relevant documents or records (from MODX, a search index, or an external service).
- Convert them into chunks of text and/or metadata suitable for AI consumption.
- Optionally work with a vector database or other retrieval mechanism.

Agents call Context Providers when they need **grounded, site‑specific** information instead of relying only on the base model.

## Typical Data Sources
Examples of what a Context Provider might expose:
- MODX **Resources** (e.g., content pages, knowledge base articles)
- MODX **Elements** (Snippets, Chunks, Templates) and their descriptions
- Indexed documentation, FAQs, or release notes
- External knowledge bases or search APIs

## Registering Context Providers
To add a new provider:
- Implement a PHP class that follows the `\modAI\Tools\ToolInterface` interface.
- Register the tool with modAI:
    - Create a plugin that will run on `modAIOnContextProviderRegister` event and will return the class name of the Context Provider or an array of multiple context providers you wish to register.
- Create the context provider from Context Providers tab:
    - Select the Context Provider class
    - A **unique name**.
    - Set the internal description explaining what the Context Provider does

Once registered, the context provider appears in the **Context Provider** tab and you’ll be able to attach it to agents.

## Configuring Context Providers in the Manager
From the **Context Providers** tab you can:
- Enable or disable providers.
- Attach providers to specific **Agents** so only the right agents can access a given data source.

Design tips:
- Use separate providers for very different datasets (e.g., “Public Docs”, “Internal Runbooks”, “Developer API Docs”).
- Keep provider scopes narrow so retrieval stays relevant and efficient.

## Performance and Cost
Context providers may rely on:
- Full‑text search queries
- Vector database lookups
- External API calls

Be mindful of:
- Token usage when passing large chunks of context into prompts.
- Rate limits and pricing for external services.
- Caching strategies to reduce repeated work.
