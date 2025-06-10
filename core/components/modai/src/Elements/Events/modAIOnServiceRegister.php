<?php
namespace modAI\Elements\Events;

class modAIOnServiceRegister extends Event
{
    public function run()
    {
        $this->modx->event->output(json_encode([
            \modAI\Services\OpenAI::class,
            \modAI\Services\Google::class,
            \modAI\Services\Anthropic::class,
            \modAI\Services\OpenRouter::class,
            \modAI\Services\CustomOpenAI::class
        ]));
    }
}
