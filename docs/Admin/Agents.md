---
sidebar_position: 3
---

# Agents

## What an Agent Is

An **Agent** is a configurable preset that bundles the other modAI building blocks into a specific, reusable AI assistant. An agent can include:

- **[Tools](Tools.md)** for specialized actions (reading/updating MODX elements, calling external APIs, running searches)
- **[Context Providers](Context-Providers.md)** for grounded, site‑specific information
- A custom **system prompt** that defines the assistant's behavior and persona
- **Model parameters** (e.g., temperature) that control response characteristics
- A specific AI **Model** (in `service/model` format, e.g. `openai/gpt-4o-mini`)

:::info
Agents are currently the **only** way to utilize Tools and Context Providers within modAI. If you want an assistant to call a Tool or retrieve from a Context Provider, you attach them to an Agent.
:::

## Creating an Agent

From the **Agents** tab in the modAI admin you can:

- Create a new agent with a **unique name** and description.
- Choose the **Model** the agent uses.
- Set a **system prompt** that frames every conversation the agent has.
- Adjust **model parameters** such as temperature.
- Attach one or more **Tools** so the agent can take actions.
- Attach one or more **Context Providers** so the agent can ground its answers in your data.

Because agents are configured in the Manager rather than in code, you can iterate on prompts, swap models, and add or remove tools without redeploying.

## Controlling Access

Access to a specific agent is controlled by assigning one or more **user groups** to it:

- If **no** user group is assigned, the agent is available to everyone who can use modAI.
- If one or more user groups are assigned, only members of those groups see the agent.
- **Sudo** users always have access to every agent, regardless of the groups specified.

See [Permissions](../Configuration/Permissions.md) for the full list of `modai_admin_agent_*` and client permissions that govern who can create, edit, and use agents.

## Design Tips

- Keep each agent **narrowly scoped** — a focused agent (e.g., "SEO Assistant" or "Support Answer Drafter") produces more predictable results than a do‑everything one.
- Bind **write or delete Tools** to admin‑only agents, and restrict those agents to trusted user groups.
- Pair an agent with only the **Context Providers** it actually needs, to keep retrieval relevant and token usage down.
