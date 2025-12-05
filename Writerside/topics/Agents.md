# Agents
## What an Agent Is
An **Agent** is a named configuration that bundles:
- A default **AI model** (and provider)
- A **system prompt** that defines the Agent’s role and behavior
- Optional **Tools** an Agent is allowed to call
- Optional **Context Providers** an Agent can use for retrieval‑augmented generation
- Access control and advanced execution options

Agents give you consistent behavior for repeated tasks (e.g., “ContentWriter”, “Dev Helper”, “Support Agent”) and allow you to centralize configuration instead of scattering prompts and settings across individual fields.

## Example Use Cases
- A marketing‑focused writer for landing pages and blog posts
- An SEO assistant for refining titles, meta descriptions, and schema
- A developer-focused helper that can inspect MODX Elements or suggest code updates
- A support or internal‑docs Agent that answers questions from indexed resources
- 
## Creating and Managing Agents
From the **Agents** tab:
- Click **Create Agent**.
- Fill in the core settings:
    - **Name** – Human‑friendly label shown in dropdowns and selectors.
    - **Description** – Short explanation of what the agent is for.
    - **Model** – The default model identifier (e.g., openai/gpt-5.1-mini, anthropic/claude-4.5-opus, google/gemini-3.0-pro, or a custom provider/model ID).
    - **Base Prompt / System Prompt** – High‑level instructions defining the Agent’s persona, guardrails, and output style.
- Optionally configure:
    - **Tools** – Select which tools this agent may call.
    - **Context Providers** – Select the providers this agent can use to pull in relevant content.
    - **User Group / Access Rules** – Restrict visibility/use of the agent to specific MODX user groups.
    - **Advanced Configuration** – Any JSON or structured options supported by the implementation (for example, temperature, max tokens, or provider‑specific flags).
- Set **Enabled = Yes** to make the agent available in the modAI UI.
- Save.

Once created, the Agent appears in:
- The Agent selector in the modAI chat modal.
- Any UI where the ✦ button is configured to allow Agent selection.

## Access Control and Permissions
Agents can be scoped so that:
- Only specific **user groups** can see or select them.
- Certain Agents are reserved for administrators (e.g., ones with tools that modify templates or chunks).
- Agents without any tools behave as “pure” chat/content Agents; those with tools can run actions on behalf of the user.

When designing agents:
- Separate **high‑risk tools** (e.g., edit or delete operations) into dedicated admin‑only agents.
- Use more constrained, read‑only Agents for general users.

## Best Practices
- Keep each Agent focused on a single domain (e.g., “ContentWriter”, “SEO Assistant”, “Developer Helper”) instead of one catchall agent.
- Put brand voice, tone, and formatting rules into the system prompt so results are consistent across your site.
- Reuse the same Agent wherever possible rather than copying prompts into multiple agents.
- Periodically review usage and refine prompts based on real outputs, feedback, and new LLM updates/options.
