<?php
/**
 *
 * THIS SCRIPT IS AUTOMATICALLY GENERATED, NO CHANGES WILL APPLY
 *
 * @package modai
 * @subpackage build
 *
 * @var \xPDO\Transport\xPDOTransport $transport
 * @var array $object
 * @var array $options
 */

use MODX\Revolution\Transport\modTransportPackage;

class Migrator
{
    private $modx;
    private $name = 'modai';
    private $latestVersion = '';
    public function __construct(&$modx)
    {
        $this->modx =& $modx;
        $this->getLatestVersion();
    }

    private function getMigrationsMap()
    {
        $migrations = [
            (function () {
                return new class() {
    // migration runs if self::VERSION > currently installed version
    const VERSION = '0.10.0-beta';

    /**
    * @var \MODX\Revolution\modX
    */
    private $modx;

    private $systemSettingsMap = [
        'modai.global.base.output' => 'modai.global.text.base_output',
        'modai.global.base.prompt' => 'modai.global.text.base_prompt',
        'modai.global.max_tokens' => 'modai.global.text.max_tokens',
        'modai.global.model' => 'modai.global.text.model',
        'modai.global.temperature' => 'modai.global.text.temperature',

        'modai.res.pagetitle.prompt' => 'modai.res.pagetitle.text.prompt',
        'modai.res.longtitle.prompt' => 'modai.res.longtitle.text.prompt',
        'modai.res.introtext.prompt' => 'modai.res.introtext.text.prompt',
        'modai.res.description.prompt' => 'modai.res.description.text.prompt',

        'modai.vision.model' => 'modai.global.vision.model',
        'modai.vision.prompt' => 'modai.global.vision.prompt',

        'modai.image.model' => 'modai.global.image.model',
        'modai.image.size' => 'modai.global.image.size',
        'modai.image.quality' => 'modai.global.image.quality',
        'modai.image.path' => 'modai.global.image.path',
        'modai.image.download_domains' => 'modai.global.image.download_domains',
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
    }
};
            })(),
            (function () {
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
            })(),
        ];

        $migrationsMap = [];

        foreach ($migrations as $migration) {
            $migrationsMap[$migration::VERSION] = $migration;
        }

        uksort($migrationsMap, 'version_compare');

        return $migrationsMap;
    }

    public function migrate()
    {
        if (empty($this->latestVersion)) return;

        $migrationsMap = $this->getMigrationsMap();

        foreach ($migrationsMap as $version => $migration) {
            if (version_compare($version, $this->latestVersion, '>')) {
                $this->modx->log(\MODX\Revolution\modX::LOG_LEVEL_INFO, 'Running migration: ' . $version);
                $migration($this->modx);
            }
        }

    }

    private function getLatestVersion()
    {
        $c = $this->modx->newQuery(modTransportPackage::class);
        $c->where([
            'workspace' => 1,
            "(SELECT
                    `signature`
                  FROM {$this->modx->getTableName(modTransportPackage::class)} AS `latestPackage`
                  WHERE `latestPackage`.`package_name` = `modTransportPackage`.`package_name`
                  ORDER BY
                     `latestPackage`.`version_major` DESC,
                     `latestPackage`.`version_minor` DESC,
                     `latestPackage`.`version_patch` DESC,
                     IF(`release` = '' OR `release` = 'ga' OR `release` = 'pl','z',`release`) DESC,
                     `latestPackage`.`release_index` DESC
                  LIMIT 1,1) = `modTransportPackage`.`signature`",
        ]);
        $c->where([
            'modTransportPackage.package_name' => $this->name,
            'installed:IS NOT' => null
        ]);

        /** @var modTransportPackage $oldPackage */
        $oldPackage = $this->modx->getObject(modTransportPackage::class, $c);
        if ($oldPackage) {
            $this->latestVersion = $oldPackage->getComparableVersion();
        }
    }
}

(new Migrator($transport->xpdo))->migrate();
