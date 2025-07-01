<?php

namespace modAI\Tools;

use MODX\Revolution\modChunk;
use MODX\Revolution\modX;

class GetChunks implements ToolInterface
{
    private $modx;

    public static function getSuggestedName(): string
    {
        return 'get_chunks';
    }

    public static function getPrompt(modX $modx): string
    {
        return "Find existing chunks in the website. Chunks are reusable pieces of HTML or other content. The tool can search for Get or search chunks (modChunk) from current MODX Revolution database. You can provide optional parameters to return only specific chunks - query: search chunks by name, broad match; name: return chunk with a given name; id: return chunk with a given ID. You'll receive an array of chunks with a properties of id, name, description, category (ID of a category) and optionally content.";
    }

    public static function getParameters(modX $modx): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    "description" => 'Simple wild-card search on the chunk name'
                ],
                'name' => [
                    'type' => 'string',
                    "description" => 'Get a single chunk by name if you already know the name'
                ],
                'id' => [
                    'type' => 'number',
                    "description" => 'Get a single chunk by its ID'
                ],
                'categories' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'number',
                    ],
                    "description" => 'List chunks that are assigned to the provided category ID(s)'
                ],
                'returnContent' => [
                    'type' => 'boolean',
                    "description" => 'Return chunk\'s content, use this only when you are tasked with updating chunk'
                ],
            ],
            "required" => []
        ];
    }

    public static function getConfig(modX $modx): array
    {
        return [];
    }

    public function __construct(modX $modx, array $config)
    {
        $this->modx = $modx;
    }

    /**
     * @param array $arguments
     * @return string
     */
    public function runTool(array $arguments): string
    {
        if (!self::checkPermissions($this->modx)) {
            return json_encode(['success' => false, "message" => "You do not have permission to use this tool."]);
        }

        $where = [];

        if (is_array($arguments)) {
            if (!empty($arguments['query'])) {
                $where[] = [
                    'name:LIKE' => '%' . $arguments['query'] . '%',
                ];
            }

            if (!empty($arguments['name'])) {
                $where[] = [
                    'name' => $arguments['query'],
                ];
            }

            if (!empty($arguments['id'])) {
                $where[] = [
                    'id' => $arguments['id'],
                ];
            }

            if (!empty($arguments['categories'])) {
                $where[] = [
                    'category:IN' => $arguments['categories'],
                ];
            }
        }

        $output = [];

        /** @var modChunk[] $chunks */
        $chunks = $this->modx->getIterator(modChunk::class, $where);
        foreach ($chunks as $chunk) {
            $arr = [
                'id' => $chunk->get('id'),
                'name' => $chunk->get('name'),
                'description' => $chunk->get('description'),
                'category' => $chunk->get('category'),
            ];

            if ($arguments['returnContent'] === true) {
                $arr['content'] = $chunk->get('content');
            }

            $output[] = $arr;
        }

        return json_encode($output);
    }

    public static function checkPermissions(modX $modx): bool
    {
        return true;
    }

    public static function getDescription(): string
    {
        return 'Find existing chunks';
    }
}
