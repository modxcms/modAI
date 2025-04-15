<?php
namespace modAI\Elements\Events;

use MODX\Revolution\modChunk;
use MODX\Revolution\modTemplate;
use MODX\Revolution\modX;

class OnTemplateRemove extends Event
{
    public function run()
    {
        $contextName = $this->modx->getOption('modai.contexts.templates.name');
        if (empty($contextName)) {
            return;
        }

        /** @var \modAI\Model\ContextProvider $provider */
        $provider = $this->modx->getObject(\modAI\Model\ContextProvider::class, ['enabled' => true, 'name' => $contextName, 'class' => \modAI\ContextProviders\Pinecone::class]);
        if (!$provider) {
            return;
        }

        /** @var modTemplate $template */
        $template = $this->getOption('template');

        try {
            /** @var \modAI\ContextProviders\Pinecone $instance */
            $instance = $provider->getContextProviderInstance();
            $instance->delete('template', [$template->id]);

        } catch (\Throwable $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[modai] context plugin: ' . $e->getMessage());
            return;
        }
    }
}
