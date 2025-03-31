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

        $tools = [
            [
                'name' => \modAI\Tools\GetWeather::getSuggestedName(),
                'class' => \modAI\Tools\GetWeather::class,
                'config' => [],
                'enabled' => true,
            ]
        ];

        foreach ($tools as $tool) {
            $exists = $this->modx->getCount(\modAI\Model\Tool::class, ['name' => $tool['name']]);
            if ($exists > 0) {
                continue;
            }

            $toolObjects = $this->modx->newObject(\modAI\Model\Tool::class, $tool);
            $toolObjects->save();
        }

        return true;
    }
};
