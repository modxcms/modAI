<?php

use MODX\Revolution\modAccessContext;
use MODX\Revolution\modAccessPermission;
use MODX\Revolution\modAccessPolicy;
use MODX\Revolution\modAccessPolicyTemplate;
use MODX\Revolution\modAccessPolicyTemplateGroup;
use MODX\Revolution\modContext;
use MODX\Revolution\modUserGroup;

return new class() {
    /**
     * @var \MODX\Revolution\modX
     */
    private $modx;

    /**
     * @var int
     */
    private $action;

    /**
    * @param \MODX\Revolution\modX $modx
    * @param int $action
    * @return bool
    */
    public function __invoke(&$modx, $action)
    {
        $this->modx =& $modx;
        $this->action = $action;

        if ($this->action === \xPDO\Transport\xPDOTransport::ACTION_UNINSTALL) {
            return true;
        }

        $group = $modx->getObject(modAccessPolicyTemplateGroup::class, ['name' => 'Administrator']);
        if (!$group) {
            return;
        }

        /** @var modAccessPolicyTemplate $template */
        $template = $modx->getObject(modAccessPolicyTemplate::class, ['name' => 'modAI', 'template_group' => $group->get('id')]);
        if (!$template) {
            $template = $modx->newObject(modAccessPolicyTemplate::class);
            $template->set('name', 'modAI');
            $template->set('template_group', $group->get('id'));
            $template->set('description', 'A policy template to for modAI');
            $template->set('lexicon', 'modai:permissions');
            $template->save();
        }

        $permissions = [
            'modai_admin',
            'modai_admin_tools',
            'modai_admin_tool_save',
            'modai_admin_tool_delete',
            'modai_admin_context_providers',
            'modai_admin_context_provider_save',
            'modai_admin_context_provider_delete',
            'modai_admin_agents',
            'modai_admin_agent_save',
            'modai_admin_agent_delete',
            'modai_admin_agent_tool_save',
            'modai_admin_agent_tool_delete',
            'modai_admin_agent_context_provider_save',
            'modai_admin_agent_context_provider_delete',
            'modai_admin_related_agent_save',
            'modai_admin_related_agent_delete',

            'modai_client',
            'modai_client_text',
            'modai_client_vision',
            'modai_client_chat_text',
            'modai_client_chat_image',
        ];

        foreach ($permissions as $permission) {
            /** @var modAccessPermission $obj */
            $obj = $modx->getObject(modAccessPermission::class, [
                'template' => $template->get('id'),
                'name' => $permission
            ]);

            if (!$obj) {
                $obj = $modx->newObject(modAccessPermission::class);
                $obj->set('template', $template->get('id'));
                $obj->set('name', $permission);
            }

            $obj->set('description', "modai.permissions.$permission");
            $obj->save();
        }

        /** @var modAccessPolicy $adminPolicy */
        $adminPolicy = $modx->getObject(modAccessPolicy::class, ['name' => 'modAI Admin']);
        if (!$adminPolicy) {
            $adminPolicy = $modx->newObject(modAccessPolicy::class);
            $adminPolicy->set('name', 'modAI Admin');
            $adminPolicy->set('description', 'Administrator policy for modAI.');
            $adminPolicy->set('template', $template->get('id'));
            $adminPolicy->set('lexicon', $template->get('lexicon'));
        }

        $data = [];

        foreach ($permissions as $permission) {
            $data[$permission] = true;
        }

        $adminPolicy->set('data', $data);
        $adminPolicy->save();

        /** @var modUserGroup $adminUserGroup */
        $adminUserGroup = $modx->getObject(modUserGroup::class, ['id' => 1]);
        if ($adminUserGroup) {
            /** @var modContext[] $contexts */
            $contexts = $modx->getIterator(modContext::class);
            foreach ($contexts as $context) {
                $contextAccess = $modx->getObject(modAccessContext::Class, ['target' => $context->get('key'), 'principal_class' => modUserGroup::class, 'principal' => 1, 'policy' => $adminPolicy->get('id')]);
                if (!$contextAccess) {
                    $contextAccess = $modx->newObject(modAccessContext::class);
                    $contextAccess->set('target', $context->get('key'));
                    $contextAccess->set('principal_class', modUserGroup::class);
                    $contextAccess->set('principal', 1);
                    $contextAccess->set('policy', $adminPolicy->get('id'));
                    $contextAccess->set('authority', 0);
                    $contextAccess->save();
                }
            }
        }

        return true;
    }
};
