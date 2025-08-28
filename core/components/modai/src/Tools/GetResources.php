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

    public static function getPrompt(modX $modx): string
    {
        return "Search for resources, also called pages or documents, on the website. You can provide optional parameters to return only specific resources. Returns matching resources with their id, pagetitle, parent resource ID, template ID, edit url, and public URL.";
    }

    public static function getParameters(modX $modx): array
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

    public static function getConfig(modX $modx): array
    {
        return [];
    }

    public function __construct(modX $modx, array $config = [])
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
        $where['deleted'] = false;
        $where['searchable'] = true;

        if (is_array($arguments)) {
            if (!empty($arguments['query'])) {
                $where[] = [
                    'pagetitle:LIKE' => '%' . $arguments['query'] . '%',
                    'OR:longtitle:LIKE' => '%' . $arguments['query'] . '%',
                    'OR:introtext:LIKE' => '%' . $arguments['query'] . '%',
                ];
            }

            if (!empty($arguments['id'])) {
                $where[] = [
                    'id' => $arguments['id'],
                ];
            }

            if (!empty($arguments['parent'])) {
                $where[] = [
                    'parent' => $arguments['parent'],
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
                'url' => $this->modx->makeUrl($resource->get('id'), '', '', 'full'),
            ];

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
        return 'Find existing resources';
    }
}
