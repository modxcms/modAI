<?php

$_lang['modai.admin.menu.home'] = 'modAI';
$_lang['modai.admin.menu.home_desc'] = 'Configure modAI';

$_lang['modai.admin.home.page_title'] = 'modAI';
$_lang['modai.admin.home.agents'] = 'Agents';
$_lang['modai.admin.home.tools'] = 'Tools';
$_lang['modai.admin.home.context_providers'] = 'Context Providers';

$_lang['modai.admin.global.no_records'] = 'No records found.';
$_lang['modai.admin.global.any'] = 'Any';

$_lang['modai.admin.context_provider.name'] = 'Name';
$_lang['modai.admin.context_provider.description'] = 'Description';
$_lang['modai.admin.context_provider.description_desc'] = 'Internal description of the context provider.';
$_lang['modai.admin.context_provider.enabled'] = 'Enabled';
$_lang['modai.admin.context_provider.enabled_desc'] = 'If disabled, context provider won\'t augument the prompt.';
$_lang['modai.admin.context_provider.create'] = 'Create Context Provider';
$_lang['modai.admin.context_provider.update'] = 'Update Context Provider';
$_lang['modai.admin.context_provider.remove'] = 'Remove Context Provider';
$_lang['modai.admin.context_provider.remove_confirm'] = 'Are you sure you want to permanently delete "[[+name]]" context provider?';
$_lang['modai.admin.context_provider.search'] = 'Search by name';
$_lang['modai.admin.context_provider.select_class_for_config'] = 'Select Context Provider Class to configure it.';
$_lang['modai.admin.context_provider.no_config'] = "Context Provider Class doesn't expose any configuration.";
$_lang['modai.admin.context_provider.config'] = "Config";
$_lang['modai.admin.context_provider.context_provider'] = "Context Provider";
$_lang['modai.admin.context_provider.class'] = "Context Provider Class";
$_lang['modai.admin.context_provider.agents'] = 'Agents';

$_lang['modai.admin.tool.name'] = 'Name';
$_lang['modai.admin.tool.description'] = 'Description';
$_lang['modai.admin.tool.description_desc'] = 'Internal description of the tool.';
$_lang['modai.admin.tool.enabled'] = 'Enabled';
$_lang['modai.admin.tool.enabled_desc'] = 'If disabled, tool won\'t be available for the model.';
$_lang['modai.admin.tool.default'] = 'Default';
$_lang['modai.admin.tool.default_desc'] = 'If enabled, tool will be available for every prompt, even without an agent.';
$_lang['modai.admin.tool.create'] = 'Create Tool';
$_lang['modai.admin.tool.update'] = 'Update Tool';
$_lang['modai.admin.tool.remove'] = 'Remove Tool';
$_lang['modai.admin.tool.remove_confirm'] = 'Are you sure you want to permanently delete "[[+name]]" tool?';
$_lang['modai.admin.tool.search'] = 'Search by name';
$_lang['modai.admin.tool.select_class_for_config'] = 'Select Tool Class to configure it.';
$_lang['modai.admin.tool.no_config'] = "Tool Class doesn't expose any configuration.";
$_lang['modai.admin.tool.config'] = "Config";
$_lang['modai.admin.tool.context_provider'] = "Tool";
$_lang['modai.admin.tool.class'] = "Tool Class";
$_lang['modai.admin.tool.tool'] = 'Tool';
$_lang['modai.admin.tool.agents'] = 'Agents';
$_lang['modai.admin.tool.prompt'] = 'Prompt';
$_lang['modai.admin.tool.prompt_desc'] = 'Description of the tool to the AI. By default it uses the default from the tool\'s class, but you can override it and customize.';

$_lang['modai.admin.agent.name'] = 'Name';
$_lang['modai.admin.agent.description'] = 'Description';
$_lang['modai.admin.agent.enabled'] = 'Enabled';
$_lang['modai.admin.agent.enabled_desc'] = 'If disabled, agent won\'t show up in the agent select.';
$_lang['modai.admin.agent.create'] = 'Create Agent';
$_lang['modai.admin.agent.update'] = 'Update Agent';
$_lang['modai.admin.agent.remove'] = 'Remove Agent';
$_lang['modai.admin.agent.remove_confirm'] = 'Are you sure you want to permanently delete "[[+name]]" agent?';
$_lang['modai.admin.agent.search'] = 'Search by name';
$_lang['modai.admin.agent.agent'] = "Agent";
$_lang['modai.admin.agent.model'] = "Model";
$_lang['modai.admin.agent.model_desc'] = "Model that will be used with this agent. Also can be set in advanced config as global.text.model.";
$_lang['modai.admin.agent.description'] = "Description";
$_lang['modai.admin.agent.config'] = "Config";
$_lang['modai.admin.agent.prompt'] = "Prompt";
$_lang['modai.admin.agent.prompt_desc'] = 'Custom prompt that will send as a system instructions.';
$_lang['modai.admin.agent.tools'] = 'Tools';
$_lang['modai.admin.agent.context_providers'] = 'Context Providers';
$_lang['modai.admin.agent.advanced_config'] = 'Advanced Config';
$_lang['modai.admin.agent.advanced_config_desc'] = 'Override any model\'s config option.';
$_lang['modai.admin.agent.advanced_config.field'] = 'Field';
$_lang['modai.admin.agent.advanced_config.area'] = 'Area';
$_lang['modai.admin.agent.advanced_config.setting'] = 'Setting';
$_lang['modai.admin.agent.advanced_config.value'] = 'Value';
$_lang['modai.admin.agent.advanced_config.add_option'] = 'Add Option';
$_lang['modai.admin.agent.advanced_config.remove_option'] = 'Remove Option';
$_lang['modai.admin.agent.user_groups'] = 'User Groups';

$_lang['modai.admin.agent_tool.create'] = 'Assign Tool';
$_lang['modai.admin.agent_tool.remove'] = 'Unassign Tool';
$_lang['modai.admin.agent_tool.view'] = 'View Tool';
$_lang['modai.admin.agent_tool.remove_confirm'] = 'Are you sure you want to unassign "[[+name]]" tool from this agent?';

$_lang['modai.admin.agent_context_provider.create'] = 'Assign Context Provider';
$_lang['modai.admin.agent_context_provider.remove'] = 'Unassign Context Provider';
$_lang['modai.admin.agent_context_provider.view'] = 'View Context Provider';
$_lang['modai.admin.agent_context_provider.remove_confirm'] = 'Are you sure you want to unassign "[[+name]]" context provider from this agent?';

$_lang['modai.admin.related_agent.agents'] = 'Agents';
$_lang['modai.admin.related_agent.create'] = 'Assign Agent';
$_lang['modai.admin.related_agent.view'] = 'View Agent';
$_lang['modai.admin.related_agent.remove'] = 'Unassign Agent';
$_lang['modai.admin.related_agent.remove_confirm'] = 'Are you sure you want to unassign "[[+name]]" agent?';

$_lang['modai.admin.context_provider.pinecone.api_key'] = 'API Key';
$_lang['modai.admin.context_provider.pinecone.api_key_desc'] = 'API Key to access Pinecone';
$_lang['modai.admin.context_provider.pinecone.endpoint'] = 'API endpoint';
$_lang['modai.admin.context_provider.pinecone.endpoint_desc'] = 'Endpoint of your Pinecone API instance.';
$_lang['modai.admin.context_provider.pinecone.namespace'] = 'Namespace';
$_lang['modai.admin.context_provider.pinecone.namespace_desc'] = 'Namespace that will be used to store/query your data.';
$_lang['modai.admin.context_provider.pinecone.fields'] = 'Fields to index';
$_lang['modai.admin.context_provider.pinecone.fields_desc'] = 'Comma separated list of fields to index.';
$_lang['modai.admin.context_provider.pinecone.fields_map'] = 'Map fields to a different name';
$_lang['modai.admin.context_provider.pinecone.fields_map_desc'] = 'Comma separated list of original_name:new_name pairs';
$_lang['modai.admin.context_provider.pinecone.context_messages'] = 'Context Messages';
$_lang['modai.admin.context_provider.pinecone.context_messages_desc'] = 'Additional context messages that will be put in front of the data from DB. One message per line. Can contain {id} or any {field} (defined in fields config) placeholder, you can also reference a system setting with using ++ as a prefix, for example {++site_url}.';


$_lang['modai.admin.error.required'] = 'Field is required.';

$_lang['modai.admin.error.context_provider_name_already_exists'] = 'Context Provider with this name already exists.';
$_lang['modai.admin.error.context_provider_wrong_interface'] = 'Context Provider class does not implement the \modAI\ContextProviders\ContextProviderInterface';
$_lang['modai.admin.error.context_provider_not_found'] = 'Context Provider was not found.';

$_lang['modai.admin.error.tool_name_already_exists'] = 'Tool with this name already exists.';
$_lang['modai.admin.error.tool_wrong_interface'] = 'Context Provider class does not implement the \modAI\Tools\ToolInterface';
$_lang['modai.admin.error.tool_not_found'] = 'Context Provider was not found.';

$_lang['modai.admin.error.agent_name_already_exists'] = 'Agent with this name already exists.';
$_lang['modai.admin.error.agent_not_found'] = 'Agent was not found.';

$_lang['modai.admin.error.agent_tool_not_found'] = "Agent's Tool was not found.";
$_lang['modai.admin.error.agent_context_provider_not_found'] = "Agent's Context Provider was not found.";
$_lang['modai.admin.error.agent_id_required'] = 'Agent id is required.';
$_lang['modai.admin.error.related_agent_tool_context_provider_required'] = 'Tool id or Context provider id is required.';
$_lang['modai.admin.error.related_agent_not_found'] = 'Related agent not found.';
