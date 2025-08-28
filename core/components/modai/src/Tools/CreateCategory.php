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

    public static function getPrompt(modX $modx): string
    {
        return "Creates a new Category that any element (chunks or templates) can be grouped by. Use the get categories tool first to check if a category already exists. When successful, this tool returns the category ID to use when creating chunks or templates.";
    }

    public static function getParameters(modX $modx): array
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

    public static function getConfig(modX $modx): array
    {
        return [];
    }

    public function __construct(modX $modx, array $config = [])
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

        $output = [];

        foreach ($arguments['categories'] as $data) {
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

    public static function getDescription(): string
    {
        return 'Creates a new category';
    }
}
