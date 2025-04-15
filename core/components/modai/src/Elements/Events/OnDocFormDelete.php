<?php
namespace modAI\Elements\Events;

use MODX\Revolution\modX;

class OnDocFormDelete extends Event
{
    public function run()
    {
        $contextName = $this->modx->getOption('modai.contexts.resources.name');
        if (empty($contextName)) {
            return;
        }

        /** @var \modAI\Model\ContextProvider $provider */
        $provider = $this->modx->getObject(\modAI\Model\ContextProvider::class, ['enabled' => true, 'name' => $contextName, 'class' => \modAI\ContextProviders\Pinecone::class]);
        if (!$provider) {
            return;
        }

        $id = $this->getOption('id');
        $children = $this->getOption('children');

        try {
            /** @var \modAI\ContextProviders\Pinecone $instance */
            $instance = $provider->getContextProviderInstance();
            $instance->delete('resource', array_merge([$id], is_array($children) ? $children : []));

        } catch (\Throwable $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[modai] context plugin: ' . $e->getMessage());
            return;
        }
    }
}
