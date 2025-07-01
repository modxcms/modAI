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

    public static function getPrompt(modX $modx): string
    {
        return "Find templates available in the website. Templates are assigned to resources and determine how a page is rendered to visitors. You can provide optional parameters to return only specific templates. You'll receive an array of templates with a properties of id, name, description, category (ID of a category) and optionally content.";
    }

    public static function getParameters(modX $modx): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    "description" => 'Search templates by a simple search on the name'
                ],
                'name' => [
                    'type' => 'string',
                    "description" => 'Get a template by its exact name'
                ],
                'id' => [
                    'type' => 'number',
                    "description" => 'Get a template by its ID'
                ],
                'categories' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'number',
                    ],
                    "description" => 'List templates within the provided categories'
                ],
                'returnContent' => [
                    'type' => 'boolean',
                    "description" => 'Include the template content in the response, use this only when you are tasked with updating template'
                ],
            ],
            "required" => []
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

        $where = [];

        if (is_array($arguments)) {
            if (!empty($arguments['query'])) {
                $where[] = [
                    'templatename:LIKE' => '%' . $arguments['query'] . '%',
                ];
            }

            if (!empty($arguments['name'])) {
                $where[] = [
                    'templatename' => $arguments['query'],
                ];
            }

            if (!empty($arguments['id'])) {
                $where[] = [
                    'id' => $arguments['id'],
                ];
            }

            if (!empty($arguments['categories'])) {
                $where[] = [
                    'category:IN' => $arguments['categories'],
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

            if ($arguments['returnContent'] === true) {
                $arr['content'] = $template->get('content');
            }

            $output[] = $arr;
        }

        return json_encode($output);
    }

    public static function checkPermissions(modX $modx): bool
    {
        return true;
    }

    public static function getDescription(): string
    {
        return 'Find existing templates';
    }
}
