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
            $contextProviders = $registeredContextProvider;

            if (!is_array($contextProviders)) {
                $maybeJSON = json_decode($registeredContextProvider, true);
                if (is_array($maybeJSON)) {
                    $contextProviders = $maybeJSON;
                } else {
                    $contextProviders = [$registeredContextProvider];
                }
            }

            if (!is_array($contextProviders)) {
                continue;
            }

            foreach ($contextProviders as $contextProvider) {
                if ($this->validateClassName($contextProvider, $query)) {
                    $classes[] = $contextProvider;
                }
            }
        }

        return $this->outputArray(array_map(function($class) {
            return [
                'class' => $class,
                'config' => $class::getConfig($this->modx),
                'description' => $class::getDescription(),
                'suggestedName' => $class::getSuggestedName(),
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
