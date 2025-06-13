<?php

namespace modAI\API\Chat;

use modAI\API\API;
use modAI\Exceptions\APIException;
use modAI\Exceptions\LexiconException;
use modAI\Model\Chat;
use modAI\Utils;
use Psr\Http\Message\ServerRequestInterface;

class CloneChat extends API
{
    public function post(ServerRequestInterface $request): void
    {
        if (!$this->modx->hasPermission('modai_client_chat_text')) {
            throw APIException::unauthorized();
        }

        $data = $request->getParsedBody();
        $chatId = Utils::getOption('chatId', $data, null);

        if (empty($chatId)) {
            throw new LexiconException('modai.error.invalid_arguments');
        }

        $chat = Chat::getPublicChat($this->modx, $chatId);
        if (!$chat) {
            throw new LexiconException('modai.error.invalid_chat');
        }

        $chat->clone();

        $this->success(['success' => true]);
    }
}
