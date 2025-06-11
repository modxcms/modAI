<?php

namespace modAI\Processors\PromptLibrary\Prompts;

use modAI\Model\PromptLibraryPrompt;
use MODX\Revolution\Processors\Model\RemoveProcessor;

class Remove extends RemoveProcessor
{
    public $classKey = PromptLibraryPrompt::class;
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.prompt_library.prompt';
    public $permission = 'modai_admin_prompt_library_prompt_delete';
}
