<?php

namespace modAI\Tools;

use MODX\Revolution\modChunk;
use MODX\Revolution\modX;

class EditChunk implements ToolInterface
{
    private $modx;

    public static function getSuggestedName(): string
    {
        return 'edit_chunk';
    }

    public static function getPrompt(): string
    {
        return "Edits an existing Chunk, which is a reusable piece of HTML or other code that can be inserted into pages, templates, or other elements. Use when explicitly asked to edit a chunk or when creating templates to break up reusable pieces. Get the current content to edit using the get_chunks tool. Chunks can be used by adding [[\$name_of_chunk]] in a template or elsewhere. ALWAYS ask for explicit user confirmation with the chunk name, description, and category name in a separate message BEFORE calling this function.";
    }

    public static function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'chunk' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => [
                            'type' => 'string',
                            "description" => 'Name of the chunk to edit'
                        ],
                        'description' => [
                            'type' => 'string',
                            "description" => 'Description of the chunk, summary of what the chunk renders'
                        ],
                        'content' => [
                            'type' => 'string',
                            "description" => 'The updated content of the chunk'
                        ],
                    ],
                    "required" => ["name", "description", 'content'],
                    "description" => "The chunk to edit",
                ],
            ],
            "required" => ["chunk"],
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
    public function runTool($arguments): string
    {
        if (!self::checkPermissions($this->modx)) {
            return json_encode(['success' => false, "message" => "You do not have permission to use this tool."]);
        }

        if (empty($arguments)) {
            return json_encode(['success' => false, 'message' => 'Parameters are required.']);
        }

        $chunk = $this->modx->getObject(modChunk::class, ['name' => $arguments['chunk']['name']]);
        if (!$chunk) {
            return json_encode(['success' => false, 'message' => 'Chunk not found with name.']);
        }
        $chunk->set('description', (string)$arguments['chunk']['description']);
        $chunk->set('snippet', (string)$arguments['chunk']['content']);
        if ($chunk->save()) {
            return json_encode(['success' => true, 'message' => 'Chunk updated. Use with: [[$' . $chunk->get('name') . ']]']);
        }

        return json_encode(['success' => false, 'message' => 'Could not save chunk.']);
    }

    public static function checkPermissions(modX $modx): bool
    {
        return $modx->hasPermission('save_chunk');
    }

    public static function getDescription(): string
    {
        return 'Allows the assistant to edit chunks.';
    }
}
