<?php

namespace modAI\Processors\Combos;

use modAI\ContextProviders\ContextProviderInterface;
use MODX\Revolution\Processors\Processor;

class ContextProviderClass extends Processor
{
    public $languageTopics = ['modai:default'];

    public function process()
    {
        $query = $this->getProperty('query');

        /** @var class-string<ContextProviderInterface>[] $classes */
        $classes = [];

        $registeredContextProviders = $this->modx->invokeEvent('modAIOnContextProviderRegister');
        foreach ($registeredContextProviders as $registeredContextProvider) {
            if ($this->validateClassName($registeredContextProvider, $query)) {
                $classes[] = $registeredContextProvider;
                continue;
            }

            if (is_array($registeredContextProvider)) {
                foreach ($registeredContextProvider as $contextProvider) {
                    if ($this->validateClassName($contextProvider, $query)) {
                        $classes[] = $contextProvider;
                    }
                }
            }
        }

        return $this->outputArray(array_map(function($class) {
            return [
                'class' => $class,
                'config' => $class::getConfig(),
            ];
        }, $classes), count($classes));
    }

    public function getLanguageTopics()
    {
        return $this->languageTopics;
    }

    private function validateClassName($class, $query)
    {
        if (!class_implements($class, ContextProviderInterface::class)) {
            return false;
        }

        if (!empty($query) && stripos($class, $query) === false) {
            return false;
        }

        return true;
    }
}
