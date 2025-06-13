<?php

namespace modAI\API\Tools;

use JsonException;
use modAI\API\API;
use modAI\Exceptions\APIException;
use modAI\Exceptions\LexiconException;
use modAI\Model\Agent;
use modAI\Model\Chat;
use modAI\Model\Message;
use modAI\Model\Tool;
use modAI\Utils;
use Psr\Http\Message\ServerRequestInterface;

class Run extends API
{
    public function post(ServerRequestInterface $request): void
    {
        if (!$this->modx->hasPermission('modai_client_text')) {
            throw APIException::unauthorized();
        }

        $data = $request->getParsedBody();
        $toolCalls = Utils::getOption('toolCalls', $data);
        $chatId = Utils::getOption('chatId', $data);
        $agent = Utils::getOption('agent', $data, null);

        if (!is_array($toolCalls)) {
            throw new \Exception('Invalid args');
        }

        if (!empty($agent)) {
            $agent = $this->modx->getObject(Agent::class, ['name' => $agent]);
            if (!$agent) {
                throw new LexiconException('modai.error.invalid_agent');
            }

            $userGroups = $this->modx->user->getUserGroups();
            $agentGroups = $agent->get('user_groups');
            if (!$this->modx->user->sudo && $agentGroups !== null) {
                $match = array_intersect($agentGroups, $userGroups);

                if (count($match) === 0) {
                    throw new LexiconException('modai.error.invalid_agent');
                }
            }
        }

        $content = [];

        $tools = Tool::getAvailableTools($this->modx, $agent ? $agent->id : null);

        foreach ($toolCalls as $toolCall) {
            if (isset($tools[$toolCall['name']])) {
                $tool = $tools[$toolCall['name']]->getToolInstance();

                try {
                    $arguments = (array) json_decode($toolCall['arguments'], true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    $arguments = [];
                }

                $content[] = [
                    'id' => $toolCall['id'],
                    'name' => $toolCall['name'],
                    'content' => $tool->runTool($arguments),
                ];
            }
        }

        $responseId = 'tools_run_' . time() . '_' . substr(base64_encode(random_bytes(8)), 0, -2);

        if (!empty($chatId)) {
            $chat = Chat::getChat($this->modx, $chatId);
            if ($chat === null) {
                throw new LexiconException('modai.error.invalid_chat');
            }

            Message::addToolResponseMessage($this->modx, $chatId, $responseId, $content);
        }

        $this->success([
            'id' => $responseId,
            'content' => $content,
        ]);
    }
}
