<?php

namespace modAI\Tools;

use MODX\Revolution\modCategory;
use MODX\Revolution\modX;

class CreateCategory implements ToolInterface
{
    private $modx;

    public static function getSuggestedName(): string
    {
        return 'create_category';
    }

    public static function getDescription(): string
    {
        return "ALWAYS ask for explicit user confirmation in a separate message before calling this function, even if user asks directly for creating categories, you HAVE TO ask for their confirmation in a separate message, provide a list of name, and parent category name that you want to create. If needed, use an appropriate tool to first list available categories. Creates new MODX categories and returns their IDs.";
    }

    public static function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'categories' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => [
                                'type' => 'string',
                                "description" => 'Name of the category'
                            ],
                            'parent_id' => [
                                'type' => 'number',
                                "description" => 'ID of the parent category, use 0 for root category.'
                            ],
                            'children' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'string',
                                ],
                                "description" => 'List of child category names.'
                            ],
                        ],
                        "required" => ["name", "parent_id"]
                    ],
                    "description" => "List of categories to create"
                ],
            ],
            "required" => ["categories"]
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
        if (!self::checkPermissions($this->modx)) {
            return json_encode(['success' => false, "message" => "You do not have permission to use this tool."]);
        }

        if (empty($parameters)) {
            return json_encode(['success' => false, 'message' => 'Parameters are required.']);
        }

        $output = [];

        foreach ($parameters['categories'] as $data) {
            $category = $this->modx->newObject(modCategory::class);
            $category->set('category', $data['name']);
            $category->set('parent', $data['parent_id']);
            $category->save();

            $output[] = [
                'id' => $category->get('id'),
                'name' => $category->get('category'),
                'parent' => $category->get('parent'),
            ];

            if (!empty($data['children'])) {
                foreach ($data['children'] as $child) {
                    $childCategory = $this->modx->newObject(modCategory::class);
                    $childCategory->set('category', $child);
                    $childCategory->set('parent', $category->get('id'));
                    $childCategory->save();

                    $output[] = [
                        'id' => $childCategory->get('id'),
                        'name' => $childCategory->get('category'),
                        'parent' => $childCategory->get('parent'),
                    ];
                }
            }
        }

        return json_encode($output);
    }

    public static function checkPermissions(modX $modx): bool
    {
        return $modx->hasPermission('save_category');
    }
}
