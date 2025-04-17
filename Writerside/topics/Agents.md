# Agents

To create an agent, navigate to the Extras -> modAI -> Agents and hit `Create Agent` button.

## Properties

### Name
Name of the agent, this text will show up in the modAI UI, in the agent selector.

### Description
Internal description of the agent, doesn't have any special functionality.

### User Groups
When defined, only users from those user groups will be able to use this agent. SUDO users have an access to every agent.

### Enabled
If set to `false`, the agent won't be available in the agent selector.

### Model
Name of the model this agent will use. If not specified, model from appropriate system setting will be used. 

### Prompt
Custom system prompt for this agent. Can give a specific persona to the agent, or instruct the AI how to use and combine specific tools.

### Advanced Config
Through advanced config, you can override any model setting (that you have in system settings), or add any additional parameters. The format is same as in system settings.
