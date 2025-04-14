<?php

namespace modAI\Tools;

use MODX\Revolution\modChunk;
use MODX\Revolution\modX;

class DeleteChunks implements ToolInterface
{
    private $modx;

    public static function getSuggestedName(): string
    {
        return 'delete_chunks';
    }

    public static function getDescription(): string
    {
        return "ALWAYS ask for explicit user confirmation in a separate message before calling this function, even if user asks directly for deletion, you HAVE TO ask for their confirmation in a separate message, call an appropriate tool first to provide a list of chunks, including their category name, you want to delete. This function deletes MODX chunks.";
    }

    public static function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'ids' => [
                    'type' => 'array',
                    'description' => 'Chunk IDs to delete.',
                    'items' => [
                        'type' => 'number'
                    ]
                ],
            ],
            "required" => ['ids']
        ];
    }

    public static function getConfig(): array
    {
        return [];
    }

    public function __construct(modX $modx, array $config)
    {
        $this->modx = $modx;
    }

    /**
     * @param array | null $parameters
     * @return string
     */
    public function runTool($parameters): string
    {
        if (!self::checkPermissions($this->modx)) {
            return json_encode(['success' => false, "message" => "You do not have permission to use this tool."]);
        }

        if (empty($parameters) || empty($parameters['ids'])) {
            return json_encode(['success' => false, 'message' => 'Parameters are required.']);
        }

        $this->modx->removeCollection(modChunk::class, ['id:IN' => $parameters['ids']]);

        return json_encode(["success" => true]);
    }

    public static function checkPermissions(modX $modx): bool
    {
        return $modx->hasPermission('delete_chunks');
    }
}
