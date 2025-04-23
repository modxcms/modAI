<?php

namespace modAI\Tools;

use MODX\Revolution\modChunk;
use MODX\Revolution\modX;

class CreateChunk implements ToolInterface
{
    private $modx;

    public static function getSuggestedName(): string
    {
        return 'create_chunk';
    }

    public static function getPrompt(): string
    {
        return "Creates a new Chunk, which is a reusable piece of HTML or other code that can be inserted into pages, templates, or other elements. Use when explicitly asked to create a chunk or when creating templates to break up reusable pieces. Once created, chunks can be rendered by using [[\$name_of_chunk]] in a template or elsewhere. ALWAYS ask for explicit user confirmation with the chunk name, description, and category name in a separate message BEFORE calling this function.";
    }

    public static function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'chunks' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => [
                                'type' => 'string',
                                "description" => 'Name of the chunk'
                            ],
                            'description' => [
                                'type' => 'string',
                                "description" => 'Description of the chunk, summary of what the chunk renders'
                            ],
                            'category_id' => [
                                'type' => 'number',
                                "description" => 'ID of a category, it MUST be obtained from an appropriate tool, DON\'T guess this value.'
                            ],
                            'content' => [
                                'type' => 'string',
                                "description" => 'HTML content of the chunk'
                            ],
                        ],
                        "required" => ["name", "description", 'content']
                    ],
                    "description" => "List of chunks to create"
                ],
            ],
            "required" => ["chunks"]
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
     * @param array $parameters
     * @return string
     */
    public function runTool($parameters): string
    {
        if (!self::checkPermissions($this->modx)) {
            return json_encode(['success' => false, "message" => "You do not have permission to use this tool."]);
        }

        if (empty($parameters)) {
            return json_encode(['success' => false, 'message' => 'Parameters are required.']);
        }

        $output = [];

        foreach ($parameters['chunks'] as $data) {
            if ($exists = $this->modx->getObject('modChunk', ['name' => $data['name']])) {
                $output[] = [
                    'id' => $exists->get('id'),
                    'name' => 'Chunk with name ' . $data['name'] . ' already exists - failed to create.',
                ];
                continue;
            }
            $chunk = $this->modx->newObject(modChunk::class);
            $chunk->set('name', $data['name']);
            $chunk->set('description', $data['description']);
            $chunk->set('category', $data['category_id']);
            $chunk->set('snippet', $data['content']);
            $chunk->save();

            $output[] = [
                'id' => $chunk->get('id'),
                'name' => $chunk->get('name'),
            ];
        }


        return json_encode($output);
    }

    public static function checkPermissions(modX $modx): bool
    {
        return $modx->hasPermission('save_chunk');
    }

    public static function getDescription(): string
    {
        return 'Creates a new chunk';
    }
}
