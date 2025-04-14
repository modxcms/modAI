<?php

namespace modAI\Processors\Tools;

use modAI\Model\Tool;
use MODX\Revolution\Processors\Model\RemoveProcessor;

class Remove extends RemoveProcessor
{
    public $classKey = Tool::class;
    public $languageTopics = ['modai:default'];
    public $objectType = 'modai.admin.tool';
    public $permission = 'modai_admin_tool_delete';
}
