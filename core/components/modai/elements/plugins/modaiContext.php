<?php
/**
 * @var \MODX\Revolution\modX $modx
 */

if (!$modx->services->has('modai')) {
    return;
}

/** @var \modAI\modAI | null $modAI */
$modAI = $modx->services->get('modai');

if ($modAI === null) {
    return;
}

if ($modx->event->name === 'OnDocFormSave' || $modx->event->name === 'OnResourceUndelete') {
    /**
     * @var \MODX\Revolution\modResource $resource
     */

    if ($resource->get('deleted')) {
        return;
    }

    $contextName = $modx->getOption('modai.contexts.resources.name');
    if (empty($contextName)) {
        return;
    }

    /** @var \modAI\Model\ContextProvider $provider */
    $provider = $modx->getObject(\modAI\Model\ContextProvider::class, ['enabled' => true, 'name' => $contextName, 'class' => \modAI\ContextProviders\Pinecone::class]);
    if (!$provider) {
        return;
    }

    try {
        /** @var \modAI\ContextProviders\Pinecone $instance */
        $instance = $provider->getContextProviderInstance();


        $data = $resource->toArray();
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }

            $data[$key] = strip_tags($value);
        }

        $instance->index('resource', $resource->get('id'), $data);
    } catch (\Throwable $e) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[modai] context plugin: ' . $e->getMessage());
        return;
    }


    return;
}

if ($modx->event->name === 'OnDocFormDelete') {
    $contextName = $modx->getOption('modai.contexts.resources.name');
    if (empty($contextName)) {
        return;
    }

    /** @var \modAI\Model\ContextProvider $provider */
    $provider = $modx->getObject(\modAI\Model\ContextProvider::class, ['enabled' => true, 'name' => $contextName, 'class' => \modAI\ContextProviders\Pinecone::class]);
    if (!$provider) {
        return;
    }

    try {
        /** @var \modAI\ContextProviders\Pinecone $instance */
        $instance = $provider->getContextProviderInstance();
        /**
         * @var $id
         * @var $children
         */
        $instance->delete(array_merge([$id], is_array($children) ? $children : []));

    } catch (\Throwable $e) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[modai] context plugin: ' . $e->getMessage());
        return;
    }


    return;
}
