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
        $this->modx =& $modx;
        $this->action = $action;

        if ($this->action === \xPDO\Transport\xPDOTransport::ACTION_UNINSTALL) {
            return true;
        }

        /** @var class-string<\modAI\Tools\ToolInterface>[] $tools */
        $tools = [
            \modAI\Tools\GetWeather::class,

            \modAI\Tools\GetCategories::class,
            \modAI\Tools\CreateCategory::class,

            \modAI\Tools\GetChunks::class,
            \modAI\Tools\CreateChunk::class,

            \modAI\Tools\GetTemplates::class,
            \modAI\Tools\CreateTemplate::class,

            \modAI\Tools\CreateResource::class,
            \modAI\Tools\GetResources::class,
            \modAI\Tools\GetResourceDetail::class,
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


        return true;
    }
};
