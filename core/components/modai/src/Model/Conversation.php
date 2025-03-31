<?php
namespace modAI\Model;

use xPDO\xPDO;

/**
 * Class Conversation
 *
 * @property string $title
 * @property int $started_by
 * @property int $started_on
 * @property int $last_message_on
 * @property boolean $visible_history
 * @property int $prompt_token_count
 * @property int $response_token_count
 *
 * @property \modAI\Model\Message[] $Messages
 *
 * @package modAI\Model
 */
class Conversation extends \xPDO\Om\xPDOSimpleObject
{
}
