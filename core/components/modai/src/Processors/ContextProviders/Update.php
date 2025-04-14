<?php

namespace modAI\Processors\ContextProviders;

use modAI\ContextProviders\ContextProviderInterface;
use modAI\Model\ContextProvider;
use MODX\Revolution\Processors\Model\UpdateProcessor;

class Update extends UpdateProcessor
{
    public $classKey = ContextProvider::class;
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.context_provider';
    public $permission = 'modai_admin_context_provider_save';

    public function beforeSet()
    {
        /** @var class-string<ContextProviderInterface> $class */
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

        if ($this->doesAlreadyExist(['name' => $name, 'id:!=' => $this->object->id])) {
            $this->addFieldError('name', $this->modx->lexicon('modai.admin.error.context_provider_name_already_exists'));
            return false;
        }

        if (!class_implements($class, ContextProviderInterface::class)) {
            $this->addFieldError('class', $this->modx->lexicon('modai.admin.error.context_provider_wrong_interface'));
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

        $this->setProperty('config', $configValues);

        return parent::beforeSet();
    }
}
