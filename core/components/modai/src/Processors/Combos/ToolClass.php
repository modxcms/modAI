<?php

namespace modAI\Processors\Combos;

use modAI\Tools\ToolInterface;
use MODX\Revolution\Processors\Processor;

class ToolClass extends Processor
{
    public $languageTopics = ['modai:default'];

    public function process()
    {
        $query = $this->getProperty('query');

        /** @var class-string<ToolInterface>[] $classes */
        $classes = [];

        $registeredTools = $this->modx->invokeEvent('modAIOnToolRegister');
        foreach ($registeredTools as $registeredTool) {
            $tools = $registeredTool;

            if (!is_array($tools)) {
                $maybeJSON = json_decode($registeredTool, true);
                if (is_array($maybeJSON)) {
                    $tools = $maybeJSON;
                } else {
                    $tools = [$registeredTool];
                }
            }

            if (!is_array($tools)) {
                continue;
            }

            foreach ($tools as $tool) {
                if ($this->validateClassName($tool, $query)) {
                    $classes[] = $tool;
                }
            }
        }

        return $this->outputArray(array_map(function($class) {
            return [
                'class' => $class,
                'config' => $class::getConfig(),
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
        if (!class_implements($class, ToolInterface::class)) {
            return false;
        }

        if (!empty($query) && stripos($class, $query) === false) {
            return false;
        }

        return true;
    }
}
