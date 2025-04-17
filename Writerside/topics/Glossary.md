# Glossary

## Service
The AI provider that processes and responds to user commands. Options include OpenAI, Google, Anthropic, OpenRouter, Custom, and others.

## Model
The specific AI model from a given [Service](#service) that generates responses to user messages. Models are typically referenced using the format `service/model`. For example: `openai/gpt-4o-mini` or `anthropic/claude-3-sonnet`.

## Context Provider
A form of [RAG](https://en.wikipedia.org/wiki/Retrieval-augmented_generation) (Retrieval-Augmented Generation) that enhances AI responses by supplying additional context from external sources. Context providers allow the AI to access relevant information from defined sources when generating responses, improving accuracy and relevance.

## Tool
A specialized function that extends the AI's capabilities beyond text generation. Tools enable the AI to perform specific actions within your MODX environment. For example, the built-in `create_resource` tool allows the AI to directly create resources in MODX based on user instructions.

## Agent
A configurable preset that bundles various components to create specific AI assistants. An agent can include:
- [Tools](#tool) for specialized actions
- [Context Providers](#context-provider) for enhanced information access
- Custom system prompts that define the AI's behavior
- Model parameters that control response characteristics
- A specific AI [Model](#model)

Agents are currently the only way to utilize tools and context providers within the system.
