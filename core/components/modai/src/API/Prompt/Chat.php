<?php

namespace modAI\API\Prompt;

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

class Chat extends API
{
    use AdditionalOptions;

    public function post(ServerRequestInterface $request): void
    {
        if (!$this->modx->hasPermission('modai_client_chat_text')) {
            throw APIException::unauthorized();
        }

        set_time_limit(0);

        $data = $request->getParsedBody();

        $userMsg = Utils::getOption('userMsg', $data, null);

        $prompt = Utils::getOption('content', $userMsg);
        $contexts = Utils::getOption('contexts', $userMsg, null);
        $attachments = Utils::getOption('attachments', $userMsg, null);

        $field = Utils::getOption('field', $data, '');
        $namespace = Utils::getOption('namespace', $data, 'modai');
        $messages = Utils::getOption('messages', $data);
        $agent = Utils::getOption('agent', $data, null);
        $model = Utils::getOption('model', $data, '');

        $lastMessageId = Utils::getOption('lastMessageId', $data, null);
        $persist = Utils::getOption('persist', $data, false);
        $chatPublic = Utils::getOption('chatPublic', $data, null);
        if ($chatPublic === null || !is_bool($chatPublic)) {
            $chatPublic = true;
        }

        $chatId = Utils::getOption('chatId', $data, null);
        if ($chatId !== null && !is_int($chatId)) {
            $chatId = null;
        }

        if (empty($prompt) && empty($messages)) {
            throw new LexiconException('modai.error.prompt_required');
        }

        if (!empty($model)) {
            Settings::setTextSetting($this->modx, $field, 'model', $model);
        }

        if (!empty($agent)) {
            /** @var Agent $agent */
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

            if (!empty($agent->model)) {
                Settings::setTextSetting($this->modx, $field, 'model', $agent->model);
            }

            $advancedConfig = $agent->get('advanced_config');
            if (!empty($advancedConfig)) {
                $cfgCustomOptions = [];
                foreach ($advancedConfig as $cfgItem) {
                    if (in_array($cfgItem['setting'], ['stream', 'model', 'base_output', 'base_prompt'])) {
                        Settings::setSetting($this->modx, "{$cfgItem['field']}.{$cfgItem['area']}.{$cfgItem['setting']}", $cfgItem['value']);
                        continue;
                    }

                    if ($cfgItem['setting'] == 'custom_options') {
                        if (!isset($cfgCustomOptions["{$cfgItem['field']}.{$cfgItem['area']}"])) {
                            $cfgCustomOptions["{$cfgItem['field']}.{$cfgItem['area']}"] = [];
                        }

                        $cfgCustomOptions["{$cfgItem['field']}.{$cfgItem['area']}"] = array_merge($cfgCustomOptions["{$cfgItem['field']}.{$cfgItem['area']}"], json_decode($cfgItem['value'], true));
                        continue;
                    }

                    if (!isset($cfgCustomOptions["{$cfgItem['field']}.{$cfgItem['area']}"])) {
                        $cfgCustomOptions["{$cfgItem['field']}.{$cfgItem['area']}"] = [];
                    }
                    $cfgCustomOptions["{$cfgItem['field']}.{$cfgItem['area']}"][$cfgItem['setting']] = $cfgItem['value'];
                }

                if (!empty($cfgCustomOptions)) {
                    foreach ($cfgCustomOptions as $customOptionsKey => $customOptionsValue) {
                        if (empty($customOptionsValue)) {
                            continue;
                        }

                        Settings::setSetting($this->modx, "$customOptionsKey.agent_options", json_encode($customOptionsValue));
                    }
                }
            }
        }

        $additionalOptions = $this->getAdditionalOptions($data, $field, 'text');

        $systemInstructions = [];

        $stream = intval(Settings::getTextSetting($this->modx, $field, 'stream', $namespace)) === 1;
        $model = Settings::getTextSetting($this->modx, $field, 'model', $namespace);
        $temperature = (float)Settings::getTextSetting($this->modx, $field, 'temperature', $namespace);
        $maxTokens = (int)Settings::getTextSetting($this->modx, $field, 'max_tokens', $namespace);
        $output = Settings::getTextSetting($this->modx, $field, 'base_output', $namespace, false);
        $base = Settings::getTextSetting($this->modx, $field, 'base_prompt', $namespace, false);
        $customOptions = Settings::getTextSetting($this->modx, $field, 'custom_options', $namespace, false);
        $agentOptions = Settings::getTextSetting($this->modx, $field, 'agent_options', $namespace, false);

        if (!empty($output)) {
            $systemInstructions[] = $output;
        }

        if (!empty($base)) {
            $systemInstructions[] = $base;
        }

        $userMessages = [];

        if (!empty($prompt)) {
            $msg = ['content' => $prompt, 'role' => 'user'];

            if (!empty($agent) && !empty($agent->prompt)) {
                $contexts = is_array($contexts) ? $contexts : [];
                $contexts[] = [
                    '__type' => 'agent',
                    'value' => $agent->prompt,
                ];
            }

            if (!empty($contexts)) {
                $msg['contexts'] = $contexts;
            }

            if (!empty($attachments)) {
                $msg['attachments'] = $attachments;
            }

            $userMessages[] = $msg;
        } else {
            if (!empty($agent) && !empty($agent->prompt)) {
                for ($i = count($messages) - 1; $i >= 0; $i--) {
                    if ($messages[$i]['role'] === 'user') {
                        if (!is_array($messages[$i]['contexts'])) {
                            $messages[$i]['contexts'] = [];
                        }

                        $messages[$i]['contexts'][] = [
                            '__type' => 'agent',
                            'value' => $agent->prompt,
                        ];
                        break;
                    }
                }
            }
        }

        $usedChatId = null;
        if (!empty($userMsg) && $persist) {
            $chat = \modAI\Model\Chat::getOrCreateChat($this->modx, $chatId, \modAI\Model\Chat::TYPE_TEXT, $chatPublic);
            if ($chat === null) {
                throw new LexiconException('modai.error.invalid_chat');
            }

            if ($chat->get('last_message_id') !== $lastMessageId) {
                throw new LexiconException('modai.error.chat_out_of_sync');
            }

            $usedChatId = $chat->get('id');
            Message::addUserMessage($this->modx, $usedChatId, $userMsg['id'], $prompt, $userMsg['hidden'], $contexts, $attachments, $userMsg['ctx']);
        }

        $tools = Tool::getAvailableTools($this->modx, $agent ? $agent->id : null);

        $aiService = AIServiceFactory::new($model, $this->modx);
        $result = $aiService->getCompletions(
            $userMessages,
            CompletionsConfig::new($model, $this->modx)
                ->tools($tools)
                ->messages($messages)
                ->options(['max_tokens' => $maxTokens, 'temperature' => $temperature], $customOptions, $agentOptions, $additionalOptions)
                ->systemInstructions($systemInstructions)
                ->stream($stream)
        )->withChatId($usedChatId);

        $this->proxyAIResponse($result);
    }
}
