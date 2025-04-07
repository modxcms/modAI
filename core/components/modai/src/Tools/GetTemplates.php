<?php

namespace modAI\Tools;

use MODX\Revolution\modCategory;
use MODX\Revolution\modTemplate;
use MODX\Revolution\modX;

class GetTemplates implements ToolInterface
{
    private $modx;

    public static function getSuggestedName(): string
    {
        return 'get_templates';
    }

    public static function getDescription(): string
    {
        return "Get or search templates (modTemplate) from current MODX Revolution database. You can provide optional parameters to return only specific templates. You'll receive an array of templates with a properties of id, name, description, category (ID of a category) and optionally content.";
    }

    public static function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    "description" => 'Search templates by name'
                ],
                'name' => [
                    'type' => 'string',
                    "description" => 'Get single template by name'
                ],
                'id' => [
                    'type' => 'number',
                    "description" => 'Get single template by ID'
                ],
                'categories' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'number',
                    ],
                    "description" => 'Search templates by multiple category IDs'
                ],
                'category' => [
                    'type' => 'number',
                    "description" => 'Search templates by category ID'
                ],
                'returnContent' => [
                    'type' => 'boolean',
                    "description" => 'Return template\'s content, use this only when you are tasked with updating template'
                ],
            ],
            "required" => []
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
     * @param array | null $parameters
     * @return string
     */
    public function runTool($parameters): string
    {
        $where = [];

        if (is_array($parameters)) {
            if (!empty($parameters['query'])) {
                $where[] = [
                    'templatename:LIKE' => '%' . $parameters['query'] . '%',
                ];
            }

            if (!empty($parameters['name'])) {
                $where[] = [
                    'templatename' => $parameters['query'],
                ];
            }

            if (!empty($parameters['id'])) {
                $where[] = [
                    'id' => $parameters['id'],
                ];
            }

            if (!empty($parameters['category'])) {
                $where[] = [
                    'category' => $parameters['category'],
                ];
            }

            if (!empty($parameters['categories'])) {
                $where[] = [
                    'category:IN' => $parameters['categories'],
                ];
            }
        }

        $output = [];

        /** @var modTemplate[] $templates */
        $templates = $this->modx->getIterator(modTemplate::class, $where);
        foreach ($templates as $template) {
            $arr = [
                'id' => $template->get('id'),
                'name' => $template->get('templatename'),
                'description' => $template->get('description'),
                'category' => $template->get('category'),
            ];

            if ($parameters['returnContent'] === true) {
                $arr['content'] = $template->get('content');
            }

            $output[] = $arr;
        }

        return json_encode($output);
    }
}
