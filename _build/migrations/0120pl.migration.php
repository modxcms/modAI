<?php

return new class() {
    // migration runs if self::VERSION > currently installed version
    const VERSION = '0.12.0-pl';

    /**
    * @var \MODX\Revolution\modX
    */
    private $modx;

    private $systemSettingsMap = [
        'modai.api.chatgpt.key' => 'modai.api.openai.key',
        'modai.api.gemini.key' => 'modai.api.google.key',
        'modai.api.claude.key' => 'modai.api.anthropic.key',
    ];

    /**
     * @param \MODX\Revolution\modX $modx
     * @return void
     */
    public function __invoke(&$modx)
    {
        $this->modx =& $modx;

        foreach ($this->systemSettingsMap as $oldKey => $newKey) {
            $oldSetting = $this->modx->getObject(\MODX\Revolution\modSystemSetting::class, ['key' => $oldKey, 'namespace' => 'modai']);
            if (!$oldSetting) {
                continue;
            }

            $newSetting = $this->modx->getObject(\MODX\Revolution\modSystemSetting::class, ['key' => $newKey, 'namespace' => 'modai']);
            if (!$newSetting) {
                continue;
            }

            $newSetting->set('value', $oldSetting->get('value'));
            $newSetting->save();
            $oldSetting->remove();
        }

        /** @var \MODX\Revolution\modSystemSetting[] $modelSystemSettings */
        $modelSystemSettings = $this->modx->getIterator(\MODX\Revolution\modSystemSetting::class, [
            'namespace' => 'modai',
            'key:LIKE' => '%.model'
        ]);

        foreach ($modelSystemSettings as $modelSystemSetting) {
            $modelSystemSetting->set('value', $this->fixModelName($modelSystemSetting->get('value')));
            $modelSystemSetting->save();
        }
    }

    private function fixModelName(string $model): string
    {
        if (
            strncmp($model, 'openai/', strlen('openai/')) === 0 ||
            strncmp($model, 'google/', strlen('google/')) === 0 ||
            strncmp($model, 'anthropic/', strlen('anthropic/')) === 0 ||
            strncmp($model, 'custom/', strlen('custom/')) === 0
        ) {
            return $model;
        }

        if (strncmp($model, 'gemini-', strlen('gemini-')) === 0) {
            return "google/$model";
        }

        if (strncmp($model, 'imagen-', strlen('imagen-')) === 0) {
            return "google/$model";
        }

        if (strncmp($model, 'claude-', strlen('claude-')) === 0) {
            return "anthropic/$model";
        }

        if (strncmp($model, 'custom_', strlen('custom_')) === 0) {
            return 'custom/' . substr($model, 7);
        }

        switch ($model) {
            case 'text-embedding-004':
            case 'learnlm-1.5-pro-experimental':
                return "google/$model";
            default:
                return "openai/$model";
        }
    }
};
