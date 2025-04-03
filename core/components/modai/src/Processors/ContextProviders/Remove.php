<?php

namespace modAI\Processors\ContextProviders;

use modAI\Model\ContextProvider;
use MODX\Revolution\Processors\Model\RemoveProcessor;

class Remove extends RemoveProcessor
{
    public $classKey = ContextProvider::class;
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.context_provider';
}
