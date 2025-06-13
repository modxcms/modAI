<?php

namespace modAI\API\Download;

use modAI\API\API;
use modAI\Exceptions\APIException;
use modAI\Exceptions\LexiconException;
use modAI\Model\Message;
use modAI\Settings;
use modAI\Utils;
use MODX\Revolution\Sources\modMediaSource;
use Psr\Http\Message\ServerRequestInterface;

class Image extends API
{
    private $allowedDomains = ['https://oaidalleapiprodscus.blob.core.windows.net'];
    private modMediaSource $source;

    public function post(ServerRequestInterface $request): void
    {
        if (!$this->modx->hasPermission('modai_client_chat_image')) {
            throw APIException::unauthorized();
        }

        if (!$this->modx->hasPermission('file_create')) {
            throw APIException::unauthorized();
        }

        $data = $request->getParsedBody();

        $url = Utils::getOption('url', $data);
        $messageId = Utils::getOption('messageId', $data);
        $field = Utils::getOption('field', $data, '');
        $namespace = Utils::getOption('namespace', $data, 'modai');
        $resource = (int)Utils::getOption('resource', $data, 0);
        $mediaSource = Utils::getOption('mediaSource', $data, '');
        $path = Utils::getOption('path', $data, '');
        $forceDownload = Utils::getOption('forceDownload', $data, false);

        if (!empty($path)) {
            Settings::setImageSetting($this->modx, $field, 'path', $path);
        }

        if (!empty($mediaSource)) {
            Settings::setImageSetting($this->modx, 'global', 'media_source', $mediaSource);
        }

        $mediaSource = Settings::getImageSetting($this->modx, $field, 'media_source', $namespace);

        if (empty($mediaSource)) {
            throw new LexiconException('modai.error.ms_required');
        }

        if (empty($url) && empty($messageId)) {
            throw new LexiconException('modai.error.image_required');
        }

        $this->initMediaSource($mediaSource);

        $additionalDomains = Settings::getImageSetting($this->modx, $field, 'download_domains', $namespace, false);
        $additionalDomains = Utils::explodeAndClean(is_string($additionalDomains) ? $additionalDomains : '');

        $allowedDomains = array_merge($additionalDomains, $this->allowedDomains);

        $urls = [];

        if (!empty($url)) {
            $urls[] = $url;
        }

        if (!empty($messageId)) {
            $msg = $this->modx->getObject(Message::class, [
                'id' => $messageId,
                'type' => 'AssistantMessage',
                'content_type' => 'image',
            ]);

            if ($msg) {
                $ctx = $msg->get('ctx');
                if (!is_array($ctx)) {
                    $ctx = [];
                }

                $allUrls = $ctx['allUrls'] ?? [];
                $allUrls[] = $msg->get('content');
                $urls = array_reverse($allUrls);
            }
        }

        $path = Settings::getImageSetting($this->modx, $field, 'path');
        $filePath = $this->createFilePath($path, $resource);

        $existing = $this->getPathFromMediaSource($urls);
        if ($forceDownload === false && $existing !== false) {
            $this->success($existing);
            return;
        }

        foreach ($urls as $url) {
            $isDataUrl = strncmp($url, 'data:', strlen('data:')) === 0;

            if (strncmp($url, '/', 1) === 0) {
                $url = MODX_URL_SCHEME . MODX_HTTP_HOST . $url;
                $domainAllowed = true;
            } else if (!$isDataUrl) {
                $domainAllowed = false;
                foreach ($allowedDomains as $domain) {
                    if (strncmp($url, $domain, strlen($domain)) === 0) {
                        $domainAllowed = true;
                        break;
                    }
                }
            } else {
                $domainAllowed = true;
            }

            if (!$domainAllowed) {
                continue;
            }

            $image = @file_get_contents($url);
            if ($image === false) {
                continue;
            }

            $this->source->createObject($filePath[0], $filePath[1], $image);

            $this->success([
                'url' => $filePath[0] . $filePath[1],
                'fullUrl' => $this->source->getObjectUrl($filePath[0] . $filePath[1])
            ]);
            return;
        }

        throw new LexiconException('modai.error.invalid_image');
    }

    private function createFilePath($path, $resource): array
    {
        $hash = md5(microtime());

        $path = str_replace('{hash}', $hash, $path);
        $path = str_replace('{shortHash}', substr($hash, 0, 4), $path);
        $path = str_replace('{resourceId}', $resource, $path);
        $path = str_replace('{year}', date('Y'), $path);
        $path = str_replace('{month}', date('m'), $path);
        $path = str_replace('{day}', date('d'), $path);

        $path = trim($path, DIRECTORY_SEPARATOR);
        $path = explode(DIRECTORY_SEPARATOR, $path);

        $fileName = array_pop($path);

        return [implode(DIRECTORY_SEPARATOR, $path) . DIRECTORY_SEPARATOR, $fileName];
    }

    private function initMediaSource($mediaSource)
    {
        if (is_int($mediaSource) || ctype_digit((string)$mediaSource)) {
            $source = $this->modx->getObject(modMediaSource::class, [
                'id' => (int)$mediaSource,
            ]);
        } else {
            $source = $this->modx->getObject(modMediaSource::class, [
                'name' => $mediaSource,
            ]);
        }

        if (!$source) {
            throw new LexiconException('modai.error.source_not_found');
        }

        if (!$source->initialize()) {
            throw new LexiconException('modai.error.source_init failed');
        }

        if (!$source->checkPolicy('create')) {
            throw APIException::unauthorized();
        }

        $this->source = $source;
    }

    private function getPathFromMediaSource(array $urls)
    {
        foreach ($urls as $url) {
            $isDataUrl = strncmp($url, 'data:', strlen('data:')) === 0;
            if ($isDataUrl) {
                continue;
            }

            $base = $this->source->getBaseUrl($url);

            if (strpos($url, $base) === 0) {
                $url = substr($url, strlen($base));
            }

            if (substr($url, 0, 1) === '/') {
                $url = ltrim($url, '/');
            }

            $exists = $this->source->getMetaData($url) !== false;
            if (!$exists) {
                continue;
            }

            $fullUrl = $this->source->getObjectUrl($url);

            return [
                'reused' => true,
                'url' => $url,
                'fullUrl' => $fullUrl,
                'allUrls' => $urls
            ];
        }

        return false;
    }
}
