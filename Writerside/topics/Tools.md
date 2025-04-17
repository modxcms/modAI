# Tools

To be able to use tools through agents, or directly from chat, they have to be created and configured from this section.

To create & configure tool, navigate to the Extras -> modAI -> Tools and hit `Create Tool` button.

## Properties

### Tool Class
Implementation of the tool. Every tool class can have an additional configuration that will show up in the `Config` panel on the right side. Additional tools can be registered using plugin with `modAIOnToolRegister` event. 

### Name
Name of the tool, this name has to be unique across all configured tools. This is how AI references a tool that it wants to run.

### Description
Internal description of the tool, doesn't have any special functionality.

### Enabled
If set to `false`, the tool won't be available for use, even when assigned to agents.

### Default
When set to `true`, the tool will be available for use in every prompt (without a need to select an agent). Be careful with enabling this on tools as it will increase token usage and can worsen the AI responses.  

### Additional Config Options
Every tool class can expose a different set of additional config options. You can reference a value from system settings by using `ss:system_setting_key` format for the config option's value.
