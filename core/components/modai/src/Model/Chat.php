<?php

namespace modAI\Model;

use MODX\Revolution\modX;
use xPDO\xPDO;

/**
 * Class Chat
 *
 * @property string $title
 * @property int $created_by
 * @property string $created_on
 * @property string $last_message_on
 * @property int $prompt_tokens
 * @property int $completion_tokens
 *
 * @property \modAI\Model\Message[] $Messages
 *
 * @package modAI\Model
 */
class Chat extends \xPDO\Om\xPDOSimpleObject
{
    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';

    public static function getOrCreateChat(modX $modx, ?int $chatId, string $type, bool $public = true)
    {
        if ($chatId === null) {
            $chat = $modx->newObject(self::class);
            $chat->set('title', 'New Chat');
            $chat->set('type', $type);
            $chat->set('public', $public);
            $chat->set('created_by', $modx->user->id);
            $chat->set('created_on', time());
            $chat->set('last_message_on', time());
            $chat->save();

            return $chat;
        }

        $chat = $modx->getObject(self::class, ['id' => $chatId, 'created_by' => $modx->user->id]);
        if (!$chat) {
            return null;
        }

        return $chat;
    }

    /**
     * @return Chat|null
     */
    public static function getChat(modX $modx, int $chatId)
    {
        $chat = $modx->getObject(self::class, ['id' => $chatId, 'created_by' => $modx->user->id]);
        if (!$chat) {
            return null;
        }

        return $chat;
    }

    /**
     * @return Chat|null
     */
    public static function getPublicChat(modX $modx, int $chatId)
    {
        $chat = $modx->getObject(self::class, [
            'id' => $chatId,
            [
                'created_by' => $modx->user->id,
                'OR:public:=' => true,
            ]
        ]);
        if (!$chat) {
            return null;
        }

        return $chat;
    }

    public static function updateLastMessage(modX $modx, int $chatId, string $lastMessageId)
    {
        $chat = self::getChat($modx, $chatId);
        if (!$chat) {
            return;
        }

        $chat->set('last_message_on', time());
        $chat->set('last_message_id', $lastMessageId);
        $chat->save();
    }

    public function syncLastMessage()
    {
        $c = $this->xpdo->newQuery(Message::class);
        $c->where([
            'chat' => $this->get('id'),
        ]);
        $c->sortby('created_on', 'DESC');
        $c->limit(1);

        $messages = $this->xpdo->getCollection(Message::class, $c);
        foreach ($messages as $message) {
            $this->set('last_message_on', $message->get('created_on'));
            $this->set('last_message_id', $message->get('id'));
            $this->save();
            return;
        }

        $this->set('last_message_on', null);
        $this->set('last_message_id', null);
        $this->save();
    }

    public function clone()
    {
        $newChat = $this->xpdo->newObject(self::class);
        $newChat->fromArray($this->toArray());
        $newChat->set('id', null);
        $newChat->set('created_by', $this->xpdo->user->id);
        $newChat->set('title', 'Clone: ' . $this->get('title'));
        $newChat->save();

        $messages = $this->getMany('Messages');
        foreach ($messages as $message) {
            $newMessage = $this->xpdo->newObject(Message::class);
            $newMessage->fromArray($message->toArray());
            $newMessage->set('internalId', null);
            $newMessage->set('chat', $newChat->get('id'));
            $newMessage->set('created_by', $this->xpdo->user->id);
            $newMessage->save();
        }

        return $newChat;
    }
}
