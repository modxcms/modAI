<?php

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
        $this->modx = &$modx;
        $this->action = $action;

        if ($this->action === \xPDO\Transport\xPDOTransport::ACTION_UNINSTALL) {
            return true;
        }

        $this->seedTools();
        $this->seedPromptLibraryRootCategories();

        return true;
    }

    private function seedTools()
    {
        /** @var class-string<\modAI\Tools\ToolInterface>[] $tools */
        $tools = [
            \modAI\Tools\GetWeather::class,

            \modAI\Tools\GetCategories::class,
            \modAI\Tools\CreateCategory::class,

            \modAI\Tools\GetChunks::class,
            \modAI\Tools\CreateChunk::class,
            \modAI\Tools\EditChunk::class,

            \modAI\Tools\GetTemplates::class,
            \modAI\Tools\CreateTemplate::class,
            \modAI\Tools\EditTemplate::class,

            \modAI\Tools\CreateResource::class,
            \modAI\Tools\GetResources::class,
            \modAI\Tools\GetResourceDetail::class,
            \modAI\Tools\EditResource::class,
        ];

        foreach ($tools as $tool) {
            $exists = $this->modx->getCount(\modAI\Model\Tool::class, ['name' => $tool::getSuggestedName()]);
            if ($exists > 0) {
                continue;
            }

            $toolObjects = $this->modx->newObject(\modAI\Model\Tool::class, [
                'class' => $tool,
                'name' => $tool::getSuggestedName(),
                'description' => $tool::getDescription(),
                'prompt' => null,
                'config' => null,
                'default' => false,
                'enabled' => true,
            ]);

            $toolObjects->save();
        }
    }

    private function seedPromptLibraryRootCategories()
    {
        $textRoot = $this->modx->getObject(\modAI\Model\PromptLibraryCategory::class, ['type' => 'text', 'parent_id' => 0]);
        if (!$textRoot) {
            $textRoot = $this->modx->newObject(\modAI\Model\PromptLibraryCategory::class);
            $textRoot->set('name', 'Text');
            $textRoot->set('type', 'text');
            $textRoot->set('parent_id', 0);
            $textRoot->set('enabled', true);
            $textRoot->set('rank', 0);
            $textRoot->save();
        }

        $imageRoot = $this->modx->getObject(\modAI\Model\PromptLibraryCategory::class, ['type' => 'image', 'parent_id' => 0]);
        if (!$imageRoot) {
            $imageRoot = $this->modx->newObject(\modAI\Model\PromptLibraryCategory::class);
            $imageRoot->set('name', 'Image');
            $imageRoot->set('type', 'image');
            $imageRoot->set('parent_id', 0);
            $imageRoot->set('enabled', true);
            $imageRoot->set('rank', 1);
            $imageRoot->save();
        }
    }
};
