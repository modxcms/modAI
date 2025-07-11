<?php

namespace modAI\Tools;

use MODX\Revolution\modTemplate;
use MODX\Revolution\modX;

class EditTemplate implements ToolInterface
{
    private $modx;

    public static function getSuggestedName(): string
    {
        return 'edit_template';
    }

    public static function getPrompt(modX $modx): string
    {
        return "Edits an existing template, which is used to render a resource on the front-end of the website. Use when explicitly asked to edit a template. In most cases, you should also check other templates to see the preferred coding style and structure of the website. Get the current content of the template with the get_templates tool. ALWAYS ask for explicit user confirmation with the template name and a summary of the changes you are making BEFORE calling this function.";
    }

    public static function getParameters(modX $modx): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'template' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => [
                            'type' => 'string',
                            "description" => 'Name of the template to edit'
                        ],
                        'description' => [
                            'type' => 'string',
                            "description" => 'Description of the template'
                        ],
                        'content' => [
                            'type' => 'string',
                            "description" => 'The updated content of the template'
                        ],
                    ],
                    "required" => ["name", "description", 'content'],
                    "description" => "The template to edit"
                ],
            ],
            "required" => ["template"]
        ];
    }

    public static function getConfig(modX $modx): array
    {
        return [];
    }

    public function __construct(modX $modx, array $config)
    {
        $this->modx = $modx;
    }

    /**
     * @param array $arguments
     * @return string
     */
    public function runTool(array $arguments): string
    {
        if (!self::checkPermissions($this->modx)) {
            return json_encode(['success' => false, "message" => "You do not have permission to use this tool."]);
        }

        if (empty($arguments)) {
            return json_encode(['success' => false, 'message' => 'Parameters are required.']);
        }

        $template = $this->modx->getObject(modTemplate::class, ['templatename' => $arguments['template']['name']]);
        if (!$template) {
            return json_encode(['success' => false, 'message' => 'Template not found with name.']);
        }
        $template->set('description', (string)$arguments['template']['description']);
        $template->set('content', (string)$arguments['template']['content']);
        if ($template->save()) {
            return json_encode(['success' => true, 'message' => 'Template updated.']);
        }

        return json_encode(['success' => false, 'message' => 'Could not save template.']);
    }

    public static function checkPermissions(modX $modx): bool
    {
        return $modx->hasPermission('save_template');
    }

    public static function getDescription(): string
    {
        return 'Allows the assistant to edit templates.';
    }
}
