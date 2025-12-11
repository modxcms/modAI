# Permissions

modAI comes with `modAI Admin` access policy, `modAI` access policy template and following permissions:

### Manager permissions
- modai_admin: Access modAI admin
- modai_admin_tools: Access Tools tab
- modai_admin_tool_save: Create/Update tools
- modai_admin_tool_delete: Delete tools
- modai_admin_context_providers: Access Context Providers tab
- modai_admin_context_provider_save: Create/Update context providers
- modai_admin_context_provider_delete: Delete context providers
- modai_admin_agents: Access Agents tab
- modai_admin_agent_save: Create/Update agents
- modai_admin_agent_delete: Delete agents
- modai_admin_agent_tool_save: Assign tool to an agent
- modai_admin_agent_tool_delete: Unassign tool from an agent
- modai_admin_agent_context_provider_save: Assign context provider to an agent
- modai_admin_agent_context_provider_delete: Unassign context provider from an agent
- modai_admin_related_agent_save: Assign agent to a tool/context provider
- modai_admin_related_agent_delete: Unassign agent from a tool/context provider
- modai_admin_prompt_library: Access Prompt Library tab
- modai_admin_prompt_library_prompt_save: Create/Update prompt
- modai_admin_prompt_library_prompt_save_public: Set prompt as public
- modai_admin_prompt_library_prompt_delete: Delete prompts
- modai_admin_prompt_library_category_save: Create/Update prompt category
- modai_admin_prompt_library_category_save_public: Set prompt category as public
- modai_admin_prompt_library_category_delete: Delete prompt category

### Client Permissions
- modai_client: Use modAI
- modai_client_text: Use modAI text generation
- modai_client_vision: Use modAI vision
- modai_client_chat_text: Use modAI chat
- modai_client_chat_image: Use modAI image generation

## Agents
Access to specific agents can be controlled via assigning one or more user groups to the specific agent. If no user group is assigned, the agent is available for everyone. Sudo users have access to every agent, no matter what users groups are specified.

## Image generation & Download
To fully enable image generation & download for a user, user needs to have `modai_client_chat_image` permission, `file_create` permission and `create` policy on the specific media source that is configured as the target for download.
