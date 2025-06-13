<?php

namespace modAI\API\Chat;

use modAI\API\API;
use modAI\Exceptions\APIException;
use modAI\Utils;
use Psr\Http\Message\ServerRequestInterface;
use modAI\Model\Chat;
use modAI\Model\Message;

class SearchChat extends API
{
    public function post(ServerRequestInterface $request): void
    {
        if (!$this->modx->hasPermission('modai_client_chat_text')) {
            throw APIException::unauthorized();
        }

        $data = $request->getParsedBody();
        $query = Utils::getOption('query', $data, null);

        $c = $this->modx->newQuery(Chat::class);
        $c->leftJoin(Message::class, 'Messages');

        $c->where([
            'Chat.created_by' => $this->modx->user->id,
            'OR:Chat.public:=' => true,
        ]);
        $c->where([
            'Chat.title:LIKE' => '%' . $query . '%',
            'OR:Messages.content:LIKE' => '%' . $query . '%',
        ]);

        $c->select(['Chat.id']);
        $c->distinct(true);
        $c->prepare();

        $c->stmt->execute();

        $chatIds = $c->stmt->fetchAll(\PDO::FETCH_COLUMN, 0);

        $chatIds = array_flip($chatIds);

        $this->success(['chats' => $chatIds]);
    }
}
