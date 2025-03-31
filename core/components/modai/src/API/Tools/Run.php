<?php

namespace modAI\API\Tools;

use modAI\API\API;
use modAI\Model\Tool;
use modAI\Tools\GetWeather;
use Psr\Http\Message\ServerRequestInterface;

class Run extends API
{
    public function post(ServerRequestInterface $request): void
    {
        $data = $request->getParsedBody();
        $toolCalls = $this->modx->getOption('toolCalls', $data);

        if (!is_array($toolCalls)) {
            throw new \Exception('Invalid args');
        }

        $content = [];

        $tools = Tool::getAvailableTools($this->modx);

        foreach ($toolCalls as $toolCall) {
            if (isset($tools[$toolCall['name']])) {
                $tool = $tools[$toolCall['name']]->getToolInstance();

                $content[] = [
                    'id' => $toolCall['id'],
                    'name' => $toolCall['name'],
                    'content' => $tool->runTool(json_decode($toolCall['arguments'], true)),
                ];
            }
        }

        $this->success([
            'id' => 'tools_run_' . time() . '_' . substr(base64_encode(random_bytes(8)), 0, -2),
            'content' => $content,
        ]);
    }
}
