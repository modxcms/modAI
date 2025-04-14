<?php

namespace modAI\Tools;

use MODX\Revolution\modChunk;
use MODX\Revolution\modResource;
use MODX\Revolution\modX;

class GetResourceDetail implements ToolInterface
{
    private $modx;

    public static function getSuggestedName(): string
    {
        return 'get_resource_detail';
    }

    public static function getDescription(): string
    {
        return "Use this tool to get more information about a resource, concept, or service you are unfamiliar with from of an array of integer resource IDs. Use the appropriate tool first to identify relevant resources on a topic. The tool will retrieve metadata, like title, description, published state, and last edit dates, as well as full HTML-formatted content. Provide at least one, or multiple resource IDs.";
    }

    public static function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'ids' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'items' => [
                        'type' => 'number'
                    ],
                    "description" => 'Array of IDs of resources to get details for.'
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

        if (empty($parameters)) {
            return json_encode(['success' => false, 'message' => 'Parameters are required.']);
        }

        $where = [
            'id:IN' => $parameters['ids'],
        ];

        $output = [];

        /** @var modResource[] $resources */
        $resources = $this->modx->getIterator(modResource::class, $where);
        foreach ($resources as $resource) {
            $content = $resource->get('content');

            try {
                $parser = $this->modx->getParser();
                $maxIterations = (int)$this->modx->getOption('parser_max_iterations', null, 10);
                $parser->processElementTags('', $content, false, false, '[[', ']]', [], $maxIterations);
                $parser->processElementTags('', $content, true, true, '[[', ']]', [], $maxIterations);
            } catch (\Throwable $e) { }

            $arr = $resource->toArray();

            $arr['content'] = $content;
            $arr['edit_url'] = $this->modx->config['manager_url'] . '?a=resource/update&id=' . $resource->get('id');
            $arr['uri'] = $this->modx->makeUrl($resource->get('id'), '', '', 'full');

            $output[$resource->get('id')] = $arr;
        }

        return json_encode($output);
    }

    public static function checkPermissions(modX $modx): bool
    {
        return true;
    }
}
