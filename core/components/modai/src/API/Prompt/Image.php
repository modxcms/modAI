<?php

namespace modAI\API\Prompt;

use modAI\API\API;
use modAI\Exceptions\APIException;
use modAI\Exceptions\LexiconException;
use modAI\Model\Agent;
use modAI\Model\Message;
use modAI\Services\AIServiceFactory;
use modAI\Services\Config\ImageConfig;
use modAI\Settings;
use modAI\Utils;
use Psr\Http\Message\ServerRequestInterface;

class Image extends API
{
    use AdditionalOptions;

    public function post(ServerRequestInterface $request): void
    {
        if (!$this->modx->hasPermission('modai_client_chat_image')) {
            throw APIException::unauthorized();
        }

        set_time_limit(0);

        $data = $request->getParsedBody();

        $userMsg = Utils::getOption('userMsg', $data, null);
        $prompt = Utils::getOption('content', $userMsg);
        $attachments = Utils::getOption('attachments', $userMsg, null);

        $field = Utils::getOption('field', $data, '');
        $namespace = Utils::getOption('namespace', $data, 'modai');
        $agent = Utils::getOption('agent', $data, null);

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

        if (empty($prompt)) {
            throw new LexiconException('modai.error.prompt_required');
        }

        if (!empty($agent)) {
            /** @var Agent $agent */
            $agent = $this->modx->getObject(Agent::class, ['name' => $agent, 'type' => 'image']);
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
                Settings::setImageSetting($this->modx, $field, 'model', $agent->model);
            }

            $advancedConfig = $agent->get('advanced_config');
            if (!empty($advancedConfig)) {
                $cfgCustomOptions = [];
                foreach ($advancedConfig as $cfgItem) {
                    if (in_array($cfgItem['setting'], ['quality', 'model', 'size', 'style', 'response_format'])) {
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

        $additionalOptions = $this->getAdditionalOptions($data, $field, 'image');

        $model = Settings::getImageSetting($this->modx, $field, 'model', $namespace);
        $size = Settings::getImageSetting($this->modx, $field, 'size', $namespace, false) ?? '';
        $quality = Settings::getImageSetting($this->modx, $field, 'quality', $namespace, false) ?? '';
        $style = Settings::getImageSetting($this->modx, $field, 'style', $namespace, false) ?? '';
        $customOptions = Settings::getImageSetting($this->modx, $field, 'custom_options', $namespace, false);
        $agentOptions = Settings::getImageSetting($this->modx, $field, 'agent_options', $namespace, false);
        $responseFormat = Settings::getImageSetting($this->modx, $field, 'response_format', $namespace, false) ?? '';

        $usedChatId = null;
        if (!empty($userMsg) && $persist) {
            $chat = \modAI\Model\Chat::getOrCreateChat($this->modx, $chatId, \modAI\Model\Chat::TYPE_IMAGE, $chatPublic);
            if ($chat === null) {
                throw new LexiconException('modai.error.invalid_chat');
            }

            if ($chat->get('last_message_id') !== $lastMessageId) {
                throw new LexiconException('modai.error.chat_out_of_sync');
            }

            $usedChatId = $chat->get('id');
            Message::addUserMessage($this->modx, $usedChatId, $userMsg['id'], $prompt, $userMsg['hidden'], null, $attachments, $userMsg['ctx']);
        }

        if (!empty($agent) && !empty($agent->prompt)) {
            $prompt = $agent->prompt . '\n' . $prompt ;
        }

        $aiService = AIServiceFactory::new($model, $this->modx);
        $result = $aiService->generateImage(
            $prompt,
            ImageConfig::new($model, $this->modx)
                ->options(['quality' => $quality, 'style' => $style, 'size' => $size, 'response_format' => $responseFormat], $customOptions, $agentOptions, $additionalOptions)
                ->attachments($attachments)
        )->withChatId($usedChatId);

        $this->proxyAIResponse($result);
    }
}
