<?php

namespace modAI\Model;

use xPDO\xPDO;

/**
 * Class Message
 *
 * @property int $conversation
 * @property string $llm_id
 * @property string $tool_call_id
 * @property string $user_role
 * @property int $user
 * @property string $content
 * @property array $tool_calls
 * @property int $created_on
 * @property int $prompt_token_count
 * @property int $response_token_count
 *
 * @package modAI\Model
 */
class Message extends \xPDO\Om\xPDOSimpleObject
{
}
