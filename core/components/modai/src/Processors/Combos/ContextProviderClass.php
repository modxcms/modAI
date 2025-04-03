<?php

namespace modAI\Processors\Combos;

use modAI\ContextProviders\ContextProviderInterface;
use MODX\Revolution\Processors\Processor;

class ContextProviderClass extends Processor
{
    public $languageTopics = ['modai:default'];

    public function process()
    {
        /** @var class-string<ContextProviderInterface>[] $classes */
        $classes = [];

        $registeredContextProviders = $this->modx->invokeEvent('modAIOnContextProviderRegister');
        foreach ($registeredContextProviders as $registeredContextProvider) {
            if (is_string($registeredContextProvider) && class_implements($registeredContextProvider, ContextProviderInterface::class)) {
                $classes[] = $registeredContextProvider;
                continue;
            }

            if (is_array($registeredContextProvider)) {
                foreach ($registeredContextProvider as $contextProvider) {
                    if (class_implements($contextProvider, ContextProviderInterface::class)) {
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
}
