<?php

namespace modAI\Processors\Combos;

use modAI\Tools\ToolInterface;
use MODX\Revolution\Processors\Processor;

class ToolClass extends Processor
{
    public $languageTopics = ['modai:default'];

    public function process()
    {
        /** @var class-string<ToolInterface>[] $classes */
        $classes = [];

        $registeredTools = $this->modx->invokeEvent('modAIOnToolRegister');
        foreach ($registeredTools as $registeredTool) {
            if (is_string($registeredTool) && class_implements($registeredTool, ToolInterface::class)) {
                $classes[] = $registeredTool;
                continue;
            }

            if (is_array($registeredTool)) {
                foreach ($registeredTool as $tool) {
                    if (class_implements($tool, ToolInterface::class)) {
                        $classes[] = $tool;
                    }
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
}
