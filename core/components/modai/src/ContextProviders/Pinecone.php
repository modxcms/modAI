<?php
namespace modAI\ContextProviders;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use modAI\Config\ConfigBuilder;
use modAI\Config\FieldBuilder;
use modAI\Exceptions\InvalidContextProviderConfig;
use modAI\Utils;
use MODX\Revolution\modX;

class Pinecone implements ContextProviderInterface
{
    private modX $modx;
    private Client $client;
    private string $namespace;
    private array $fields;
    private array $outputFields;
    private string $idField;
    private array $fieldsMap;
    private array $contextMessages;

    public function __construct(modX $modx, array $config = [])
    {
        $this->modx = $modx;

        $endpoint = Utils::getConfigValue($modx, 'endpoint', $config, '');
        $apiKey = Utils::getConfigValue($modx,'api_key', $config, '');
        $apiVersion = Utils::getConfigValue($modx,'api_version', $config, '2025-04');
        $this->namespace = Utils::getConfigValue($modx,'namespace', $config, '');
        $this->contextMessages = Utils::explodeAndClean(Utils::getConfigValue($modx,'context_messages', $config, ''), "\n");
        $this->fields = Utils::explodeAndClean(Utils::getConfigValue($modx,'fields', $config, ''));
        $this->outputFields = Utils::explodeAndClean(Utils::getConfigValue($modx,'output_fields', $config, ''));
        $this->idField = Utils::getConfigValue($modx,'id_field', $config, '');
        $fieldsMap = Utils::explodeAndClean(Utils::getConfigValue($modx,'fields_map', $config, ''));

        $this->fieldsMap = [];

        foreach ($fieldsMap as $value) {
            $value = Utils::explodeAndClean($value, ':', true);
            if (count($value) !== 2) {
                continue;
            }

            $this->fieldsMap[$value[0]] = $value[1];
        }


        if (empty($endpoint) || empty($apiKey) || empty($this->namespace)) {
            throw new InvalidContextProviderConfig();
        }

        $this->client = new Client([
            'base_uri' => $endpoint,
            'headers' => [
                'Api-Key' => $apiKey,
                'Content-Type' => 'application/json',
                'X-Pinecone-API-Version' => $apiVersion,
            ],
        ]);
    }

    public function index($type, $id, array $data): bool
    {
        if (empty($this->fields)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Pinecone invalid configuration: you have to specify at least one field to index.');
            return false;
        }

        $metadata = [];

        $text = [];

        foreach ($this->fields as $field) {
            if (isset($data[$field])) {
                $sourceField = $field;
                $targetField = $field;

                if (isset($this->fieldsMap[$sourceField])) {
                    $targetField = $this->fieldsMap[$sourceField];
                }

                if (!isset($data[$sourceField])) {
                    continue;
                }

                $metadata[$targetField] = $data[$sourceField];
                $text[] = $data[$sourceField];
            }
        }

        $data = [
                'id' => "{$type}_$id",
                "{$type}_id" => $id,
                'type' => $type,
                'text' => implode('\n', $text),
            ] + $metadata;

        try {
            $response = $this->client->post("/records/namespaces/$this->namespace/upsert", [
                'json' => $data
            ]);

            if ($response->getStatusCode() > 300) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, 'Pinecone non-200 response: ' . $response->getBody()->getContents());
                return false;
            }

            return true;
        } catch (RequestException $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Pinecone exception: ' . $e->getResponse()->getBody()->getContents());
            return false;
        } catch (\Throwable $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Pinecone exception: ' . $e->getMessage());
            return false;
        }
    }

    public function delete(string $type, array $ids): bool
    {
        try {
            $response = $this->client->post("vectors/delete", [
                'json' => [
                    'ids' => array_map(function ($id) use ($type) {
                        return "{$type}_$id";
                    }, $ids),
                    'namespace' => $this->namespace,
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (RequestException $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Pinecone exception: ' . $e->getResponse()->getBody()->getContents());
            return false;
        } catch (\Throwable $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Pinecone exception: ' . $e->getMessage());
            return false;
        }
    }

    public function provideContext(string $prompt): array
    {
        try {
            $response = $this->client->post("records/namespaces/$this->namespace/search", [
                'json' => [
                    'query' => [
                        'inputs' => ['text' => $prompt],
                        'top_k' => 5,
                    ],
                    'rerank' => [
                        'model' => 'bge-reranker-v2-m3',
                        'top_n' => 3,
                        'rank_fields' => ['text'],
                    ]
                ],
            ]);

            $json = json_decode($response->getBody()->getContents(), true);
            $augmented = [];

            foreach ($json['result']['hits'] as $hit) {
                $context = [];

                foreach ($this->contextMessages as $contextMessage) {
                    $context[] = $this->formatMessage($contextMessage, $hit);
                }

                foreach ($this->outputFields as $field) {
                    if ($field === 'id' || $field === '_id') {
                        $context[] = $field . ': ' . $hit['_id'];
                        continue;
                    }

                    if (!empty($hit['fields'][$field])) {
                        $context[] = $field . ': ' . $hit['fields'][$field];
                    }
                }

                $augmented[] = implode('\n', $context);
            }

            return $augmented;
        } catch (\Throwable $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Pinecone exception: ' . $e->getMessage());
            return [];
        }
    }

    private function formatMessage($msg, $data): string
    {
        $matches = [];
        preg_match_all('/{([^}]*)}/', $msg, $matches);

        $placeholders = $matches[1];

        if (!is_array($placeholders)) {
            return $msg;
        }

        $search = ['{id}'];
        $replace = [];

        if (!empty($this->idField)) {
            $replace[] = $this->parseId($data);
        } else {
            $replace[] = $data['_id'];
        }

        foreach ($placeholders as $key) {
            if (strpos($key, '++') === 0) {
                $value = substr($key, 2);
                $systemSettingValue = $this->modx->getOption($value, null, '');

                $search[] = '{' . $key . '}';
                $replace[] = $systemSettingValue;

                continue;
            }

            if (isset($data['fields'][$key])) {
                $search[] = '{' . $key . '}';
                $replace[] = $data['fields'][$key];
            }
        }

        return str_replace($search, $replace, $msg);
    }

    private function parseId($data): string {
        $lowerIdField = strtolower($this->idField);
        if ($lowerIdField === 'id' || $lowerIdField === '_id') {
            return $data['_id'];
        }

        $matches = [];
        preg_match_all('/{([^}]*)}/', $this->idField, $matches);

        $placeholders = $matches[1];

        if (empty($placeholders)) {
            return $this->idField;
        }

        $search = [];
        $replace = [];

        foreach ($placeholders as $key) {
            if (strpos($key, '++') === 0) {
                $value = substr($key, 2);
                $systemSettingValue = $this->modx->getOption($value, null, '');

                $search[] = '{' . $key . '}';
                $replace[] = $systemSettingValue;

                continue;
            }

            if (isset($data['fields'][$key])) {
                $search[] = '{' . $key . '}';
                $replace[] = $data['fields'][$key];
            }
        }

        return $data['fields'][str_replace($search, $replace, $this->idField)];
    }

    public static function getConfig(modX $modx): array
    {
        return ConfigBuilder::new($modx, 'modai.admin.context_provider.pinecone.{key}', 'modai.admin.context_provider.pinecone.{key}_desc')
            ->addField('api_key', function (FieldBuilder $fieldBuilder) use ($modx) {
                return $fieldBuilder
                    ->type('text-password')
                    ->required()
                ;
            })
            ->addField('endpoint', function (FieldBuilder $fieldBuilder) use ($modx) {
                return $fieldBuilder->required();
            })
            ->addField('api_version')
            ->addField('namespace', function (FieldBuilder $fieldBuilder) use ($modx) {
                return $fieldBuilder->required();
            })
            ->addField('id_field')
            ->addField('fields')
            ->addField('fields_map')
            ->addField('output_fields')
            ->addField('context_messages', function (FieldBuilder $fieldBuilder) use ($modx) {
                return $fieldBuilder
                    ->type('textarea')
                    ->extra('grow', true)
                ;
            })
            ->build()
        ;
    }

    public static function getDescription(): string
    {
        return 'Pinecone\'s vector database';
    }

    public static function getSuggestedName(): string
    {
        return 'pinecone';
    }
}
