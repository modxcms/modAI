<?php

namespace modAI\Processors\Download;

use modAI\RequiredSettingException;
use modAI\Settings;
use modAI\Utils;
use MODX\Revolution\Processors\Processor;
use MODX\Revolution\Sources\modMediaSource;

class Image extends Processor {
    private $allowedDomains = ['https://oaidalleapiprodscus.blob.core.windows.net'];

    public function process() {
        $url = $this->getProperty('url');
        $image = $this->getProperty('image');
        $field = $this->getProperty('field', '');
        $namespace = $this->getProperty('namespace', 'modai');
        $resource = (int)$this->getProperty('resource', 0);
        $mediaSource = (int)$this->getProperty('mediaSource', 0);

        if (empty($mediaSource)) {
            try {
                $mediaSource = Settings::getImageSetting($this->modx, $field, 'media_source', $namespace);
            } catch (RequiredSettingException $e) {
                return $this->failure($e->getMessage());
            }
        }

        if (empty($mediaSource)) {
            return $this->failure($this->modx->lexicon('modai.error.ms_required'));
        }

        if (empty($url) && empty($image)) {
            return $this->failure($this->modx->lexicon('modai.error.image_required'));
        }

        $additionalDomains = Settings::getSetting($this->modx, 'image.download_domains', '');
        $additionalDomains = Utils::explodeAndClean($additionalDomains);

        $allowedDomains = array_merge($additionalDomains, $this->allowedDomains);

        if (!empty($url)) {
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
            return $this->failure($this->modx->lexicon('modai.error.image_download_domain'));
        }

        $source = modMediaSource::getDefaultSource($this->modx, $mediaSource);

        if (!$source->initialize()) {
            return $this->failure('fail');
        }

        try {
            $path = Settings::getImageSetting($this->modx, $field, 'path');
        } catch (\Exception $e) {
            return $this->failure($e->getMessage());
        }

        $filePath = $this->createFilePath($path, $resource);

        $image = file_get_contents(empty($url) ? $image : $url);

        $source->createObject($filePath[0], $filePath[1], $image);

        return $this->success('', [
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

    public function getLanguageTopics() {
        return ['modai:default'];
    }
}
