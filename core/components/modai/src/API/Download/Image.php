<?php

namespace modAI\API\Download;

use modAI\API\API;
use modAI\Exceptions\LexiconException;
use modAI\Settings;
use modAI\Utils;
use MODX\Revolution\Sources\modMediaSource;
use Psr\Http\Message\ServerRequestInterface;

class Image extends API {
    private $allowedDomains = ['https://oaidalleapiprodscus.blob.core.windows.net'];

    public function post(ServerRequestInterface $request): void
    {
        $data = $request->getParsedBody();

        $url = $this->modx->getOption('url', $data);
        $field = $this->modx->getOption('field', $data, '');
        $namespace = $this->modx->getOption('namespace', $data, 'modai');
        $resource = (int)$this->modx->getOption('resource', $data, 0);
        $mediaSource = $this->modx->getOption('mediaSource', $data, '');

        if (empty($mediaSource)) {
            $mediaSource = Settings::getImageSetting($this->modx, $field, 'media_source', $namespace);
        }

        if (empty($mediaSource)) {
            throw new LexiconException('modai.error.ms_required');
        }

        if (empty($url) && empty($image)) {
            throw new LexiconException('modai.error.image_required');
        }

        $additionalDomains = Settings::getSetting($this->modx, 'image.download_domains', '');
        $additionalDomains = Utils::explodeAndClean($additionalDomains);

        $allowedDomains = array_merge($additionalDomains, $this->allowedDomains);

        if (!empty($url) && (strncmp($url, 'data:', strlen('data:')) !== 0)) {
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
            throw new LexiconException('modai.error.image_download_domain');
        }

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

        $path = Settings::getImageSetting($this->modx, $field, 'path');
        $filePath = $this->createFilePath($path, $resource);

        $image = file_get_contents($url);

        $source->createObject($filePath[0], $filePath[1], $image);

        $this->success([
            'url' => $filePath[0].$filePath[1],
            'fullUrl' => $source->getObjectUrl($filePath[0].$filePath[1])
        ]);
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
}
