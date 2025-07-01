<?php

namespace modAI\Tools;

use MODX\Revolution\modCategory;
use MODX\Revolution\modX;

class GetCategories implements ToolInterface
{
    private $modx;

    public static function getSuggestedName(): string
    {
        return 'get_categories';
    }

    public static function getPrompt(modX $modx): string
    {
        return "Lists all categories used to group templates and chunks. Each category can have a parent to create a tree like structure. If parent is 0, the category is at root level.";
    }

    public static function getParameters(modX $modx): array
    {
        return [];
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

        /** @var modCategory[] $chunks */
        $categories = $this->modx->getIterator(modCategory::class);
        foreach ($categories as $category) {
            $output[] =  [
                'id' => $category->get('id'),
                'name' => $category->get('category'),
                'parent' => $category->get('parent'),
            ];
        }

        return json_encode($output);
    }

    public static function checkPermissions(modX $modx): bool
    {
        return true;
    }

    public static function getDescription(): string
    {
        return 'List all categories';
    }
}
