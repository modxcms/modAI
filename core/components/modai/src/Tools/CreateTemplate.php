<?php

namespace modAI\Tools;

use MODX\Revolution\modTemplate;
use MODX\Revolution\modX;

class CreateTemplate implements ToolInterface
{
    private $modx;

    public static function getSuggestedName(): string
    {
        return 'create_template';
    }

    public static function getPrompt(): string
    {
        return "Creates a new template for the website, which can be assigned to resources to determine how they are rendered in the frontend. Only use when explicitly asked to create a new template, and always check if appropriate templates already exist first. Always ask for explicit user confirmation in a separate message, providing the user with the template name and a summary of what the contents would be, BEFORE calling this function. Do NOT output the full template content. When generating template content you MUST make sure it uses the MODX templating syntax.";
    }

    public static function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'templates' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => [
                                'type' => 'string',
                                "description" => 'Name of the template'
                            ],
                            'description' => [
                                'type' => 'string',
                                "description" => 'Description of the template, summary of what the template renders'
                            ],
                            'category_id' => [
                                'type' => 'number',
                                "description" => 'ID of a category, it MUST be obtained from an appropriate tool, DON\'T guess this value.'
                            ],
                            'content' => [
                                'type' => 'string',
                                "description" => 'HTML content of the template'
                            ],
                        ],
                        "required" => ["name", "description", 'content']
                    ],
                    "description" => "List of templates to create"
                ],
            ],
            "required" => ["templates"]
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
    public function runTool($arguments): string
    {
        if (!self::checkPermissions($this->modx)) {
            return json_encode(['success' => false, "message" => "You do not have permission to use this tool."]);
        }

        if (empty($arguments)) {
            return json_encode(['success' => false, 'message' => 'Parameters are required.']);
        }

        $output = [];

        foreach ($arguments['templates'] as $data) {
            if ($exists = $this->modx->getObject(modTemplate::class, ['templatename' => $data['name']])) {
                $output[] = [
                    'id' => $exists->get('id'),
                    'name' => 'Template with name ' . $data['name'] . ' already exists - failed to create.',
                ];
                continue;
            }
            $chunk = $this->modx->newObject(modTemplate::class);
            $chunk->set('templatename', $data['name']);
            $chunk->set('description', $data['description']);
            $chunk->set('category', $data['category_id']);
            $chunk->set('content', $data['content']);
            $chunk->save();

            $output[] = [
                'id' => $chunk->get('id'),
                'name' => $chunk->get('name'),
            ];
        }


        return json_encode($output);
    }

    public static function checkPermissions(modX $modx): bool
    {
        return $modx->hasPermission('save_template');
    }

    public static function getDescription(): string
    {
        return 'Creates a new template';
    }
}
