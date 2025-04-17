# Context Providers

To be able to use context providers through agents, they have to be created and configured from this section.

To create & configure context provider, navigate to the Extras -> modAI -> Context Providers and hit `Create Context Provider` button.

## Properties

### Context Provider Class
Implementation of the context provider. Every context provider class can have an additional configuration that will show up in the `Config` panel on the right side. Additional context providers can be registered using plugin with `modAIOnContextProviderRegister` event.

### Name
Name of the context provider, this name has to be unique across all configured context providers.

### Description
Internal description of the context provider, doesn't have any special functionality.

### Enabled
If set to `false`, the context provider won't be available for use, even when assigned to agents.

### Additional Config Options
Every context provider class can expose a different set of additional config options. You can reference a value from system settings by using `ss:system_setting_key` format for the config option's value.
