<?php

namespace modAI\Processors\PromptLibrary\Categories;

use modAI\Model\PromptLibraryCategory;
use MODX\Revolution\Processors\Model\RemoveProcessor;

class Remove extends RemoveProcessor
{
    public $classKey = PromptLibraryCategory::class;
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.prompt_library.category';
    public $permission = 'modai_admin_prompt_library_category_delete';
}
