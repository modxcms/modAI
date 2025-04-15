<?php
namespace modAI\ContextProviders;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use modAI\Exceptions\InvalidContextProviderConfig;
use modAI\Utils;
use MODX\Revolution\modX;

class Pinecone implements ContextProviderInterface
{
    private modX $modx;
    private Client $client;
    private string $namespace;
    private array $fields;
    private array $fieldsMap;
    private array $contextMessages;

    public function __construct(modX $modx, array $config = [])
    {
        $this->modx = $modx;

        $endpoint = Utils::getConfigValue($modx, 'endpoint', $config, '');
        $apiKey = Utils::getConfigValue($modx,'api_key', $config, '');
        $this->namespace = Utils::getConfigValue($modx,'namespace', $config, '');
        $this->contextMessages = Utils::explodeAndClean(Utils::getConfigValue($modx,'context_messages', $config, ''), "\n");
        $this->fields = Utils::explodeAndClean(Utils::getConfigValue($modx,'fields', $config, ''));
        $fieldsMap = Utils::explodeAndClean(Utils::getConfigValue($modx,'fields_map', $config, ''));

        $this->fieldsMap = [];

        foreach ($fieldsMap as $value) {
            $value = Utils::explodeAndClean($value, ':', true);
            if (count($value) !== 2) {
                continue;
            }

            $this->fieldsMap[$value[0]] = $value[1];
        }


        if (empty($endpoint) || empty($apiKey) || empty($this->namespace) || empty($this->fields)) {
            throw new InvalidContextProviderConfig();
        }

        $this->client = new Client([
            'base_uri' => $endpoint,
            'headers' => [
                'Api-Key' => $apiKey,
                'Content-Type' => 'application/json',
                'X-Pinecone-API-Version' => '2025-01',
            ],
        ]);
    }

    public function index($type, $id, array $data): bool
    {
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
                    $context[] = $this->formatMessage($contextMessage, $hit['fields']);
                }

                $context[] = 'id: ' . $hit['fields']["{$hit['fields']["type"]}_id"];

                if (!empty($this->link)) {
                    $context[] = (!empty($this->linkMsg) ? $this->formatMessage($this->linkMsg, $hit['fields']) : '') . $this->formatMessage($this->link, $hit['fields']);
                }

                foreach ($this->fields as $field) {
                    $targetField = $field;

                    if (isset($this->fieldsMap[$field])) {
                        $targetField = $this->fieldsMap[$field];
                    }

                    if (!empty($hit['fields'][$targetField])) {
                        $context[] = $targetField . ': ' . $hit['fields'][$targetField];
                    }
                }

                $augmented[] = implode('\n', $context);
            }

            return $augmented;
        } catch (\Throwable $e) {
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
        $replace = [$data["{$data["type"]}_id"]];

        foreach ($placeholders as $key) {
            if ($key === 'id') {
                $search[] = "\{$key\}";
                $replace[] = $data["{$data["type"]}_id"];
                continue;
            }

            if (strpos($key, '++') === 0) {
                $value = substr($key, 2);
                $systemSettingValue = $this->modx->getOption($value, null, '');

                $search[] = '{' . $key . '}';
                $replace[] = $systemSettingValue;

                continue;
            }

            if (isset($data[$key])) {
                $search[] = '{' . $key . '}';
                $replace[] = $data[$key];
            }
        }

        return str_replace($search, $replace, $msg);
    }

    public static function getConfig(): array
    {
        return [
            'api_key' => [
                'name' => 'API Key',
                'description' => 'API Key to access Pinecone',
                'required' => true,
                'type' => 'text-password'
            ],
            'endpoint' => [
                'name' => 'API endpoint',
                'description' => 'Endpoint of your Pinecone API instance.',
                'required' => true,
                'type' => 'textfield'
            ],
            'namespace' => [
                'name' => 'Namespace',
                'description' => 'Namespace that will be used to store/query your data.',
                'required' => true,
                'type' => 'textfield'
            ],
            'fields' => [
                'name' => 'Fields to index',
                'description' => 'Comma separated list of fields to index.',
                'required' => true,
                'type' => 'textfield'
            ],
            'fields_map' => [
                'name' => 'Map fields to a different name',
                'description' => 'Comma separated list of original_name:new_name pairs',
                'required' => false,
                'type' => 'textfield'
            ],
            'context_messages' => [
                'name' => 'Context Messages',
                'description' => 'Additional context messages that will be put in front of the data from DB. One message per line. Can contain {id} or any {field} (defined in fields config) placeholder, you can also reference a system setting with using ++ as a prefix, for example {++site_url}.',
                'required' => false,
                'type' => 'textarea',
                'extraProperties' => [
                    'grow' => true,
                ],
            ],
        ];
    }
}
