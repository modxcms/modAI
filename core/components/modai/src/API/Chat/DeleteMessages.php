<?php

namespace modAI\API\Chat;

use modAI\API\API;
use modAI\Exceptions\APIException;
use modAI\Exceptions\LexiconException;
use modAI\Model\Message;
use modAI\Utils;
use Psr\Http\Message\ServerRequestInterface;

class DeleteMessages extends API
{
    public function post(ServerRequestInterface $request): void
    {
        if (!$this->modx->hasPermission('modai_client_chat_text')) {
            throw APIException::unauthorized();
        }

        $data = $request->getParsedBody();

        $chatId = Utils::getOption('chatId', $data, null);
        $fromMessageId = Utils::getOption('fromMessageId', $data, null);

        if ($chatId !== null && !is_int($chatId)) {
            $chatId = null;
        }

        if (empty($chatId)) {
            throw new LexiconException('modai.error.chat_required');
        }

        if (empty($fromMessageId)) {
            throw new LexiconException('modai.error.invalid_arguments');
        }

        $msg = $this->modx->getObject(Message::class, [
            'id' => $fromMessageId,
            'chat' => $chatId,
        ]);

        if (!$msg) {
            throw new LexiconException('modai.error.invalid_arguments');
        }

        $chat = \modAI\Model\Chat::getChat($this->modx, $chatId);
        if ($chat === null) {
            throw APIException::notFound();
        }

        $this->modx->removeCollection(Message::class, [
            'chat' => $chatId,
            [
                'id' => $fromMessageId,
                'OR:created_on:>=' => $msg->get('created_on'),
            ]
        ]);

        $chat->syncLastMessage();

        $this->success([
            'success' => true,
        ]);
    }
}
