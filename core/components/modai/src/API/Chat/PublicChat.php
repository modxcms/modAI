<?php

namespace modAI\API\Chat;

use modAI\API\API;
use modAI\Exceptions\APIException;
use modAI\Exceptions\LexiconException;
use modAI\Model\Chat;
use modAI\Utils;
use Psr\Http\Message\ServerRequestInterface;

class PublicChat extends API
{
    public function post(ServerRequestInterface $request): void
    {
        if (!$this->modx->hasPermission('modai_client_chat_text')) {
            throw APIException::unauthorized();
        }

        $data = $request->getParsedBody();
        $chatId = Utils::getOption('chatId', $data, null);
        $publicStatus = Utils::getOption('publicStatus', $data, null);

        if ($publicStatus === null || empty($chatId)) {
            throw new LexiconException('modai.error.invalid_arguments');
        }

        $chat = Chat::getChat($this->modx, $chatId);
        if (!$chat) {
            throw new LexiconException('modai.error.invalid_chat');
        }

        $chat->set('public', $publicStatus);
        $chat->save();

        $this->success(['success' => true]);
    }
}
