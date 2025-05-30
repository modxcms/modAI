<?php
namespace modAI\Elements\Events;

class modAIOnToolRegister extends Event
{
    public function run()
    {
        $this->modx->event->output(json_encode([
            \modAI\Tools\GetWeather::class,

            \modAI\Tools\GetCategories::class,
            \modAI\Tools\CreateCategory::class,

            \modAI\Tools\GetChunks::class,
            \modAI\Tools\CreateChunk::class,
            \modAI\Tools\EditChunk::class,

            \modAI\Tools\GetTemplates::class,
            \modAI\Tools\CreateTemplate::class,
            \modAI\Tools\EditTemplate::class,

            \modAI\Tools\CreateResource::class,
            \modAI\Tools\GetResources::class,
            \modAI\Tools\GetResourceDetail::class,
        ]));
    }
}
