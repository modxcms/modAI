<?php
namespace modAI;

use modAI\Exceptions\LexiconException;

class Utils {
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
    public static function parseDataURL($dataURL) {
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
}
