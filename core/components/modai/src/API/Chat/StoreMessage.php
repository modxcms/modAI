<?php

namespace modAI\API\Chat;

use modAI\API\API;
use modAI\Exceptions\APIException;
use modAI\Exceptions\LexiconException;
use modAI\Model\Agent;
use modAI\Model\Message;
use modAI\Model\Tool;
use modAI\Services\AIServiceFactory;
use modAI\Services\Config\CompletionsConfig;
use modAI\Settings;
use modAI\Utils;
use Psr\Http\Message\ServerRequestInterface;

class StoreMessage extends API
{
    public function post(ServerRequestInterface $request): void
    {
        if (!$this->modx->hasPermission('modai_client_chat_text')) {
            throw APIException::unauthorized();
        }

        $data = $request->getParsedBody();

        $msg = Utils::getOption('msg', $data, null);
        $usage = Utils::getOption('usage', $data, null);
        $chatId = Utils::getOption('chatId', $data, null);

        if ($chatId !== null && !is_int($chatId)) {
            $chatId = null;
        }

        if (empty($msg) || empty($chatId)) {
            throw new LexiconException('modai.error.msg_chat_required');
        }

        $chat = \modAI\Model\Chat::getChat($this->modx, $chatId);
        if ($chat === null) {
            throw new LexiconException('modai.error.invalid_chat');
        }

        Message::addMessage($this->modx, $chat->get('id'), $msg, $usage);

        $this->success(['success' => true]);
    }
}
