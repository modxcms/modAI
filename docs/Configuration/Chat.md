---
sidebar_position: 5
---

# Chat

Beyond the inline "sparkle button" fields, modAI ships a full **chat assistant** — a conversational UI built into the MODX Manager. The chat can generate text or images, remembers your conversations, and (when paired with [Agents](../Admin/Agents.md)) can call [Tools](../Admin/Tools.md) and pull from [Context Providers](../Admin/Context-Providers.md).

## Opening the chat

When `modai.init.global_chat` is enabled (it is by default), modAI adds an **AI assistant button** (a bot icon) to the main Manager left-hand menu. Click it to open the chat modal.

The button only appears for users who have the `modai_client` permission **plus** at least one of `modai_client_chat_text` or `modai_client_chat_image` (see [Permissions](Permissions.md)).

To hide the global button, set `modai.init.global_chat` to `No`.

## Text and image chats

The chat has two modes:

- **Text** – conversational text generation. Requires `modai_client_chat_text`.
- **Image** – image generation. Requires `modai_client_chat_image`.

You can switch modes within the modal (subject to your permissions). Each mode keeps its own agents and history.

## Chat history

Chats are **persisted in the database**, so your conversations are saved between sessions and listed in the sidebar. The sidebar shows your own chats plus any [public chats](#public-chats), sorted **pinned first**, then by most recent activity.

From the sidebar you can:

- **Pin / unpin** a chat to keep it at the top of the list.
- **Rename** a chat (titles can also be [generated automatically](#automatic-chat-titles)).
- **Search** your chats — matches on both the chat title and the message content.
- **Clone** a chat, duplicating it (and all its messages) into a new conversation.
- **Delete** a chat, or delete a message and everything after it.

:::note
All history features (the sidebar, search, pin, clone, rename, delete) require the **`modai_client_chat_text`** permission specifically. A user with only `modai_client_chat_image` can generate images in the modal but won't have the persistent chat history features.
:::

## Public chats

New chats default to **public**. In modAI, "public" means the chat is visible to **other Manager users** who have access to modAI — it is *not* an anonymous, externally shareable link, and there is no front-end route that exposes chats outside the Manager.

- Other users see a public chat as **view-only**: they can read it and **clone** it, but cannot rename, pin, delete, or change its visibility.
- Only the **owner** can toggle a chat between **Make Public** and **Make Private**.

Use the sidebar's "show public chats" toggle to include or hide other people's public chats in your list.

## Automatic chat titles

When `modai.chat.title.generate` is enabled (default), modAI generates a short title from your **first message** in a conversation. This is a separate, lightweight AI call controlled by its own settings:

| Setting | Default | Purpose |
| --- | --- | --- |
| `modai.chat.title.generate` | `Yes` | Whether to auto-generate titles. |
| `modai.chat.title.model` | `openai/gpt-5-nano` | Model used to generate the title. |
| `modai.chat.title.prompt` | *(see below)* | System instruction for the title model. |
| `modai.chat.title.model_options` | `{"max_tokens": 16, "temperature": 0.7}` | JSON options for the title call. |

The default prompt asks for a title of 32 characters or fewer with minimal punctuation. Title generation additionally requires the **`modai_client_text`** permission.

## Using agents in chat

If one or more [Agents](../Admin/Agents.md) are available for the current mode, an **agent dropdown** appears in the chat input. Selecting an agent changes the conversation in several ways:

- The agent's **model** and advanced configuration (model, streaming, base prompt/output, custom options) are applied.
- The agent's **system prompt** is injected into the conversation.
- The agent's attached **Tools** become callable, and its attached **Context Providers** are queried to ground responses.

Agents are filtered by type, so text agents appear in text chats and image agents in image chats. Access to each agent can be restricted by user group — see [Agents → Controlling Access](../Admin/Agents.md#controlling-access).

:::info
Tools are available in a chat when they are either marked as **default** (available in every prompt) **or** explicitly attached to the selected agent. See [Tools](../Admin/Tools.md).
:::

## Media browser integration

When `modai.init.media_browser` is enabled (default), modAI adds an AI **generate** button to the MODX Media browser toolbar. It opens an **image** chat scoped to the active media source and path, and wires the generated image's download back into the browser — handy for creating hero or Open Graph images right where you manage files.

Set `modai.init.media_browser` to `No` to remove it.

## Additional controls

You can expose additional controls (select boxes) in the chat, for both text and image mode. This lets you present the user with extra options, like selecting a specific model or image quality.

### Format

To expose additional controls, update system setting `modai.chat.additional_controls` with JSON that follows this schema:

```JSON
{
  "type": "object",
  "properties": {
    "image": {
      "description": "Additional controls for the image mode",
      "type": "array",
      "items": [
        {
          "type": "object",
          "properties": {
            "name": {
              "type": "string",
              "description": "Name of the option, will be passed to the model"
            },
            "label": {
              "type": "string",
              "description": "Display name for the user"
            },
            "icon": {
              "type": "string",
              "description": "Optional icon in a form of SVG HTML tag"
            },
            "values": {
              "type": "object",
              "description": "value: label pairs, where `value` will be passed to the model and `label` will be displayed to the user"
            }
          },
          "required": [
            "name",
            "label",
            "values"
          ]
        }
      ]
    },
    "text": {
      "description": "Additional controls for the text mode",
      "type": "array",
      "items": [
        {
          "type": "object",
          "properties": {
            "name": {
              "type": "string",
              "description": "Name of the option, will be passed to the model"
            },
            "label": {
              "type": "string",
              "description": "Display name for the user"
            },
            "icon": {
              "type": "string",
              "description": "Optional icon in a form of SVG HTML tag"
            },
            "values": {
              "type": "object",
              "description": "value: label pairs, where `value` will be passed to the model and `label` will be displayed to the user"
            }
          },
          "required": [
            "name",
            "label",
            "values"
          ]
        }
      ]
    }
  }
}
```

### Example

Here's an example that lets the user select `Default`, `4o mini`, or `4o` as the model in text mode, and `Default`, `Low`, or `High` quality in image mode.

```JSON
{
  "image": [
    {
      "name": "quality",
      "label": "Quality",
      "values": {
        "low": "Low",
        "high": "High"
      }
    }
  ],
  "text": [
    {
      "name": "model",
      "icon": "<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='lucide lucide-activity'><path d='M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2'></path></svg>",
      "label": "Model",
      "values": {
        "openai/gpt-4o-mini": "4o mini",
        "openai/gpt-4o": "4o"
      }
    }
  ]
}

```

## Permissions reference

| Permission | Grants |
| --- | --- |
| `modai_client` | Base access to any modAI client feature. |
| `modai_client_chat_text` | Text chat and all chat history features (sidebar, search, pin, public, clone, rename, delete). |
| `modai_client_chat_image` | Image generation in the chat. |
| `modai_client_text` | Required for automatic chat-title generation. |

See [Permissions](Permissions.md) for the full list.
