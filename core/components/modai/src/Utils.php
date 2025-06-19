<?php

namespace modAI;

use modAI\Exceptions\LexiconException;
use MODX\Revolution\modX;

class Utils
{
    public static function explodeAndClean(string $stringArray, string $delimiter = ',', bool $keepDuplicates = false): array
    {
        $array = explode($delimiter, $stringArray);
        $array = array_map('trim', $array);

        if ($keepDuplicates == 0) {
            $array = array_keys(array_flip($array));
        }

        return array_filter($array);
    }

    /**
     * @param $dataURL
     * @return array|mixed
     * @throws LexiconException
     */
    public static function parseDataURL($dataURL)
    {
        if (strpos($dataURL, 'data:') !== 0) {
            return $dataURL;
        }

        if (preg_match('/^data:([^;]+);base64,(.+)$/', $dataURL, $matches)) {
            return [
                'mimeType' => $matches[1],
                'base64' => $matches[2]
            ];
        }

        throw new LexiconException('modai.error.invalid_data_url');
    }

    public static function getConfigValue(modX $modx, $name, $config, $default)
    {
        $value = $modx->getOption($name, $config, $default);
        if (strncmp($value, 'ss:', 3) === 0) {
            $value = $modx->getOption(substr($value, 3), null, $default);
        }

        return $value;
    }

    public static function convertToBoolean($value)
    {
        if ($value === true) return true;

        if (is_string($value)) {
            $value = strtolower($value);
        }

        if ($value === 'true') return true;
        if ($value === '1') return true;

        return false;
    }
}
