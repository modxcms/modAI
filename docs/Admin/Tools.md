---
sidebar_position: 1
---

# Tools

## What a Tool Is

A **Tool** is a server‑side capability that an agent can call when it needs structured data or needs to perform an action.

In modAI, Tools are generally implemented as PHP classes that:
- Declare their **name**, **description/prompt**, and **parameters**
- Implement logic to perform an action (e.g., read or update a MODX element, call an external API, run a search)
- Return structured results to the agent 
 
Think of Tools as “functions” an agent can call to go beyond plain text generation.

## Built-in Tools

modAI ships with a set of tools that are **seeded and enabled on install**. They are *not* marked as "default", which means they only run when [attached to an Agent](Agents.md) (see [Availability](#availability) below).

| Tool | What it does |
| --- | --- |
| `get_resources` | Find existing resources (matches pagetitle/longtitle/introtext; excludes deleted and non-searchable). |
| `get_resource_detail` | Get full details for one or more resources by ID. |
| `create_resource` | Create one or more resources (created unpublished). |
| `edit_resource` | Edit an existing resource's content by ID (preserves MODX `[[ ]]` tags). |
| `get_templates` | Find existing templates. |
| `create_template` | Create one or more templates. |
| `edit_template` | Edit an existing template by name. |
| `get_chunks` | Find existing chunks. |
| `create_chunk` | Create one or more chunks. |
| `edit_chunk` | Edit an existing chunk by name. |
| `get_categories` | List all element categories. |
| `create_category` | Create one or more categories (with optional children). |
| `get_weather` | Get current weather for a location (a simple demonstration tool). |

The write tools (`create_*` / `edit_*`) enforce the matching MODX permission for the current user (e.g. `save_document`, `save_chunk`, `save_template`, `save_category`), so a tool can never let a user do something they couldn't do manually. The read/`get_*` tools and `get_weather` have no permission gate.

Need something else — calling a third-party API, triggering indexing, summarizing external data? Build a [custom tool](#registering-tools).

### The `clear_cache` config

The six write tools (`create_resource`, `edit_resource`, `create_template`, `edit_template`, `create_chunk`, `edit_chunk`) expose a **`clear_cache`** config option (a yes/no field, enabled by default). When enabled, modAI refreshes the relevant MODX caches after the tool changes content.

## Registering Tools
Tools are typically registered via configuration and/or service discovery. At a high level, you’ll:
- Create a PHP class that implements the `\modAI\Tools\ToolInterface` interface.
- Register the tool with modAI:
    - Create a plugin that will run on `modAIOnToolRegister` event and will return the class name of the tool or an array of multiple Tools you wish to register.
- Create the tool from Tools tab:
    - Select the Tool class
    - A **unique name** (e.g., `EditChunk`, `SearchResources`).
    - Set the internal description explaining what the Tool does
    - Adjust the **prompt** if needed

Once registered, the tool appears in the **Tools** tab and you’ll be able to attach it to agents.

### The `ToolInterface`

A custom tool implements `\modAI\Tools\ToolInterface`:

```php
public function __construct(modX $modx, array $config = []);

// The default tool name, pre-filled when an admin configures it.
public static function getSuggestedName(): string;

// Internal description shown to admins.
public static function getDescription(): string;

// Natural-language instructions passed to the model explaining when/how to use the tool.
public static function getPrompt(modX $modx): string;

// Whether the current user is allowed to run the tool.
public static function checkPermissions(modX $modx): bool;

// The parameters the model must supply, as a JSON-Schema array.
public static function getParameters(modX $modx): array;

// Admin-configurable options for the tool (rendered as a config form). May be empty.
public static function getConfig(modX $modx): array;

// Execute the tool; returns a string (typically JSON-encoded).
public function runTool(array $arguments): string;
```

The `getConfig()` form is built with modAI's `ConfigBuilder` (the same helper used by Context Providers), so you can expose typed fields to admins without writing UI code.

## Configuring Tools in the Manager
From the **Tools** tab you can:
- Enable or disable individual Tools.
- Edit the **user‑facing name** and **prompt/description**.
- Set whether a Tool is **default** (available in every prompt, even without an agent).
- Edit any tool-specific config (such as `clear_cache`).

Changes here do not require you to redeploy code, which makes it a safe place to iterate on prompts and defaults.

### Availability

A tool runs in a conversation when **either**:

- it is marked **default** (so it is offered in every prompt), **or**
- it is **attached to the Agent** the user has selected.

A tool must also be **enabled**, and the user must pass the tool's own `checkPermissions()`. The built-in tools are enabled but *not* default, so attach them to an Agent (or mark them default) to make them callable.

## Security Considerations
Because tools can read or modify data:
- Ensure tools check **permissions** for the current MODX user.
- Avoid exposing write or delete Tools to general users; instead, bind them to admin‑only Agents.
- Validate and sanitize all input arguments.
- Log usage for sensitive operations, especially those that change content or configuration.
