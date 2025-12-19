# Tools

## What a Tool Is

A **Tool** is a server‑side capability that an agent can call when it needs structured data or needs to perform an action.

In modAI, Tools are generally implemented as PHP classes that:
- Declare their **name**, **description/prompt**, and **parameters**
- Implement logic to perform an action (e.g., read or update a MODX element, call an external API, run a search)
- Return structured results to the agent 
 
Think of Tools as “functions” an agent can call to go beyond plain text generation.

## Common Tool Examples
- Edit or create MODX **Templates**, **Chunks**, or **Snippets**
- Fetch and return content from specific Resources
- Trigger indexing jobs for a Context Provider
- Call a third‑party API (e.g., analytics, search, CRM) and return summarized data

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

## Configuring Tools in the Manager
From the **Tools** tab you can:
- Enable or disable individual Tools.
- Edit the **user‑facing name** and **prompt/description**.
- Whether a Tool is available in every prompt even without an agent

Changes here do not require you to redeploy code, which makes it a safe place to iterate on prompts and defaults.

## Security Considerations
Because tools can read or modify data:
- Ensure tools check **permissions** for the current MODX user.
- Avoid exposing write or delete Tools to general users; instead, bind them to admin‑only Agents.
- Validate and sanitize all input arguments.
- Log usage for sensitive operations, especially those that change content or configuration.
