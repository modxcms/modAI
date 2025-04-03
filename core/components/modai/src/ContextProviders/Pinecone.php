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
    private string $introMsg;

    public function __construct(modX $modx, array $config = [])
    {
        $this->modx = $modx;

        $endpoint = Utils::getConfigValue($modx, 'endpoint', $config, '');
        $apiKey = Utils::getConfigValue($modx,'api_key', $config, '');
        $this->namespace = Utils::getConfigValue($modx,'namespace', $config, '');
        $this->introMsg = Utils::getConfigValue($modx,'intro_msg', $config, '');
        $this->fields = Utils::explodeAndClean(Utils::getConfigValue($modx,'fields', $config, ''));

        if (empty($endpoint) || empty($apiKey) || empty($this->namespace) || empty($this->fields) || empty($this->introMsg)) {
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
                $metadata[$field] = $data[$field];
                $text[] = $data[$field];
            }
        }

        $data = [
                'id' => (string)$id,
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

    public function delete(array $ids): bool
    {
        try {
            $response = $this->client->post("vectors/delete", [
                'json' => [
                    'ids' => array_map('strval', $ids),
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

                $context[] = $this->formatIntroMessage($hit['fields']);
                foreach ($this->fields as $field) {
                    if (!empty($hit['fields'][$field])) {
                        $context[] = $field . ': ' . $hit['fields'][$field];
                    }
                }

                $augmented[] = implode('\n', $context);
            }

            return $augmented;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function formatIntroMessage($data): string
    {
        $search = [];
        $replace = [];

        foreach ($data as $key => $value) {
            $search[] = '{' . $key . '}';
            $replace[] = $value;
        }

        return str_replace($search, $replace, $this->introMsg);
    }

    public static function getConfig(): array
    {
        return [];
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
            'intro_msg' => [
                'name' => 'Intro message',
                'description' => 'Intro message to the found context. Can contain {id} or any {field} (defined in fields config) placeholder.',
                'required' => true,
                'type' => 'textfield'
            ]
        ];
    }
}
