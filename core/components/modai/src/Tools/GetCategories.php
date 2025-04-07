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

    public static function getDescription(): string
    {
        return "Get all categories in the current MODX Revolution database. Categories are used to separate chunks, snippets, templates, template variables and plugins. Each category can have parent to create a tree like structure. If parent is 0, the category is at root level.";
    }

    public static function getParameters(): array
    {
        return [];
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
}
