<?php
namespace modAI\Elements\Events;

use modAI\ContextProviders\Pinecone;

class modAIOnContextProviderRegister extends Event
{
    public function run()
    {
        $this->modx->event->output(Pinecone::class);
    }
}
