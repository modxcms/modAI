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

    public static function getDescription(): string
    {
        return "ALWAYS ask for explicit user confirmation in a separate message before calling this function, even if user asks directly for creating template, you HAVE TO ask for their confirmation in a separate message, provide a list of name, description and category name, DON'T output content, that you want to create. If needed, use an appropriate tool to first create categories, wait for it's response and then continue with calling this tool. Creates new MODX templates. Don't ask for the parameters, if they were not provided, make them up.";
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

    public static function getConfig(): array
    {
        return [];
    }

    public function __construct(modX $modx, array $config)
    {
        $this->modx = $modx;
    }

    /**
     * @param array $parameters
     * @return string
     */
    public function runTool($parameters): string
    {
        if (empty($parameters)) {
            throw new \Exception('Parameters are required.');
        }

        $output = [];

        foreach ($parameters['templates'] as $data) {
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
}
