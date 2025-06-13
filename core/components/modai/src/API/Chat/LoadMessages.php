<?php

namespace modAI\API\Chat;

use modAI\API\API;
use modAI\Exceptions\APIException;
use modAI\Exceptions\LexiconException;
use modAI\Model\Message;
use modAI\Utils;
use Psr\Http\Message\ServerRequestInterface;

class LoadMessages extends API
{
    public function post(ServerRequestInterface $request): void
    {
        if (!$this->modx->hasPermission('modai_client_chat_text')) {
            throw APIException::unauthorized();
        }

        $data = $request->getParsedBody();

        $chatId = Utils::getOption('chatId', $data, null);

        if ($chatId !== null && !is_int($chatId)) {
            $chatId = null;
        }

        if (empty($chatId)) {
            throw new LexiconException('modai.error.chat_required');
        }

        $chat = \modAI\Model\Chat::getPublicChat($this->modx, $chatId);
        if ($chat === null) {
            throw APIException::notFound();
        }

        $c = $this->modx->newQuery(Message::class);
        $c->where([
            'chat' => $chatId,
        ]);
        $c->sortby('created_on', 'ASC');

        $messages = $this->modx->getIterator(Message::class, $c);

        $data = [];
        foreach ($messages as $message) {
            $msgType = $message->get('type');

            $content = $message->get('content');
            if ($msgType === 'ToolResponseMessage') {
                $content = json_decode($content, true);
            }

            $ctx = $message->get('ctx');
            $metadata = $message->get('metadata');

            $data[] = [
                'id' => $message->get('id'),
                '__type' => $msgType,
                'role' => $message->get('role'),
                'content' => $content,
                'contentType' => $message->get('content_type'),
                'toolCalls' => $message->get('tool_calls'),
                'contexts' => $message->get('contexts'),
                'attachments' => $message->get('attachments'),
                'metadata' => is_null($metadata) ? null : (object)$metadata,
                'ctx' => is_null($ctx) ? null : (object)$ctx,
                'hidden' => $message->get('hidden'),
            ];
        }

        $this->success([
            'messages' => $data,
            'chat' => [
                'title' => $chat->get('title'),
                'view_only' => $chat->get('created_by') !== $this->modx->user->id,
            ]
        ]);
    }
}
