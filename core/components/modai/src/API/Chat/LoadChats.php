<?php

namespace modAI\API\Chat;

use modAI\API\API;
use modAI\Exceptions\APIException;
use modAI\Model\Chat;
use Psr\Http\Message\ServerRequestInterface;

class LoadChats extends API
{
    public function post(ServerRequestInterface $request): void
    {
        if (!$this->modx->hasPermission('modai_client_chat_text')) {
            throw APIException::unauthorized();
        }

        $c = $this->modx->newQuery(Chat::class);
        $c->where([
            'created_by' => $this->modx->user->id,
            'OR:public:=' => true,
        ]);
        $c->sortby('pinned', 'DESC');
        $c->sortby('last_message_on', 'DESC');

        $chats = $this->modx->getIterator(Chat::class, $c);

        $data = [];
        foreach ($chats as $chat) {
            $data[] = [
                'id' => $chat->get('id'),
                'title' => $chat->get('title'),
                'type' => $chat->get('type'),
                'pinned' => $chat->get('pinned'),
                'public' => $chat->get('public'),
                'view_only' => $chat->get('created_by') !== $this->modx->user->id,
                'last_message_on' => strtotime($chat->get('last_message_on')) * 1000,
            ];
        }

        $this->success(['chats' => $data]);
    }
}
