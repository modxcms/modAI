<?php

namespace modAI\Tools;

use MODX\Revolution\modChunk;
use MODX\Revolution\modResource;
use MODX\Revolution\modX;

class GetResources implements ToolInterface
{
    private $modx;

    public static function getSuggestedName(): string
    {
        return 'get_resources';
    }

    public static function getDescription(): string
    {
        return "Get or search resources (modResource) from current MODX Revolution database. You can provide optional parameters to return only specific resources. You'll receive an array of resources with a properties of id, pagetitle, parent (ID of another resource), template (ID of a template) and edit url.";
    }

    public static function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    "description" => 'Search resources by pagetitle, longtitle or introtext'
                ],
                'id' => [
                    'type' => 'number',
                    "description" => 'Get single resource by ID'
                ],
                'parent' => [
                    'type' => 'number',
                    "description" => 'Get all resources under specific parent'
                ],
            ],
            "required" => []
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
        $where = [];

        if (is_array($parameters)) {
            if (!empty($parameters['query'])) {
                $where[] = [
                    'pagetitle:LIKE' => '%' . $parameters['query'] . '%',
                    'OR:longtitle:LIKE' => '%' . $parameters['query'] . '%',
                    'OR:introtext:LIKE' => '%' . $parameters['query'] . '%',
                ];
            }

            if (!empty($parameters['id'])) {
                $where[] = [
                    'id' => $parameters['id'],
                ];
            }

            if (!empty($parameters['parent'])) {
                $where[] = [
                    'parent' => $parameters['parent'],
                ];
            }
        }

        $output = [];

        /** @var modResource[] $resources */
        $resources = $this->modx->getIterator(modResource::class, $where);
        foreach ($resources as $resource) {
            $arr = [
                'id' => $resource->get('id'),
                'pagetitle' => $resource->get('pagetitle'),
                'parent' => $resource->get('parent'),
                'template' => $resource->get('template'),
                'edit_url' => $this->modx->config['manager_url'] . '?a=resource/update&id=' . $resource->get('id'),
            ];

            $output[] = $arr;
        }

        return json_encode($output);
    }
}
