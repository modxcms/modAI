<?php

namespace modAI\Processors\Tools;

use modAI\Model\Tool;
use modAI\Tools\ToolInterface;
use MODX\Revolution\Processors\Model\CreateProcessor;

class Create extends CreateProcessor
{
    public $classKey = Tool::class;
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.tool';
    public $permission = 'modai_admin_tool_save';

    public function beforeSet()
    {
        /** @var class-string<ToolInterface> $class */
        $class = $this->getProperty('class');
        if (empty($class)) {
            $this->addFieldError('class', $this->modx->lexicon('modai.admin.error.required'));
            return false;
        }

        $name = $this->getProperty('name');
        if (empty($name)) {
            $this->addFieldError('name', $this->modx->lexicon('modai.admin.error.required'));
            return false;
        }

        if ($this->doesAlreadyExist(['name' => $name])) {
            $this->addFieldError('name', $this->modx->lexicon('modai.admin.error.tool_name_already_exists'));
            return false;
        }

        if (!class_implements($class, ToolInterface::class)) {
            $this->addFieldError('class', $this->modx->lexicon('modai.admin.error.tool_wrong_interface'));
            return false;
        }

        $config = $class::getConfig();
        $configValues = [];
        foreach ($config as $key => $options) {
            $configValues[$key] = $this->getProperty("config_$key");
            if ($options['required'] === true && empty($configValues[$key])) {
                $this->addFieldError("config_$key", $this->modx->lexicon('modai.admin.error.required'));
                return false;
            }
        }

        $this->setProperty('config', empty($configValues) ? null : $configValues);

        return parent::beforeSet();
    }
}
