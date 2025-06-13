<?php

namespace modAI\Model;

use MODX\Revolution\modX;
use xPDO\xPDO;

/**
 * Class Message
 *
 * @property int $conversation
 * @property string $type
 * @property string $llm_id
 * @property string $role
 * @property string $content
 * @property string $content_type
 * @property array $tool_calls
 * @property array $contexts
 * @property array $attachments
 * @property array $metadata
 * @property array $ctx
 * @property boolean $hidden
 * @property int $prompt_tokens
 * @property int $completion_tokens
 * @property string $created_on
 * @property int $created_by
 *
 * @package modAI\Model
 */
class Message extends \xPDO\Om\xPDOObject
{
    public static function addUserMessage(modX $modx, int $chatId, string $msgId, string $content, bool $hidden, ?array $contexts, ?array $attachments, ?array $ctx)
    {
        $msg = $modx->newObject(self::class);
        $msg->set('chat', $chatId);
        $msg->set('type', 'UserMessage');
        $msg->set('id', $msgId);
        $msg->set('role', 'user');
        $msg->set('content', $content);
        $msg->set('contexts', $contexts);
        $msg->set('attachments', $attachments);
        $msg->set('ctx', $ctx);
        $msg->set('hidden', $hidden);
        $msg->set('created_on', time());
        $msg->set('created_by', $modx->user->id);
        $msg->save();

        Chat::updateLastMessage($modx, $chatId, $msgId);

        return $msg;
    }

    public static function addMessage(modX $modx, int $chatId, array $data, array $usage = null)
    {
        $msg = $modx->getObject(self::class, ['id' => $data['id'], 'chat' => $chatId]);
        if (!$msg) {
            $msg = $modx->newObject(self::class);
            $msg->set('chat', $chatId);
            $msg->set('id', $data['id']);

            Chat::updateLastMessage($modx, $chatId, $data['id']);
        }

        $msg->set('type', $data['__type']);
        $msg->set('role', $data['role']);
        $msg->set('content', $data['content']);
        $msg->set('content_type', $data['contentType']);
        $msg->set('tool_calls', $data['toolCalls']);
        $msg->set('contexts', $data['contexts']);
        $msg->set('attachments', $data['attachments']);
        $msg->set('metadata', $data['metadata']);
        $msg->set('ctx', $data['ctx']);
        $msg->set('hidden', $data['hidden']);

        if (is_array($usage)) {
            $msg->set('prompt_tokens', $usage['promptTokens'] ?? 0);
            $msg->set('completion_tokens', $usage['completionTokens'] ?? 0);
        }

        $msg->set('created_on', time());
        $msg->set('created_by', $modx->user->id);
        $msg->save();

        return $msg;
    }

    public static function addToolResponseMessage(modX $modx, int $chatId, string $responseId, array $content)
    {
        $msg = $modx->newObject(self::class);
        $msg->set('chat', $chatId);
        $msg->set('type', 'ToolResponseMessage');
        $msg->set('id', $responseId);
        $msg->set('role', 'tool');
        $msg->set('content', json_encode($content));
        $msg->set('hidden', true);
        $msg->set('created_on', time());
        $msg->set('created_by', $modx->user->id);
        $msg->save();

        Chat::updateLastMessage($modx, $chatId, $responseId);

        return $msg;
    }
}
