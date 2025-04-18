<?php

namespace modAI\Tools;

use MODX\Revolution\modResource;
use MODX\Revolution\modX;

class CreateResource implements ToolInterface
{
    private $modx;

    public static function getSuggestedName(): string
    {
        return 'create_resource';
    }

    public static function getPrompt(): string
    {
        return "Creates a new resource, or page, on the website. ALWAYS ask for explicit user confirmation in a separate message before calling this function, even if user asks directly to create a resource, ask the user to confirm the resource before calling the tool with the information you intend to use. ";
    }

    public static function getParameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'resources' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'pagetitle' => [
                                'type' => 'string',
                                "description" => 'The title for the resource. Generate this automatically for the user.'
                            ],
                            'template' => [
                                'type' => 'number',
                                "description" => 'The ID of the template to use for the new resource. Use the appropriate tool to get a list of templates or create a new one if needed.'
                            ],
                            'parent' => [
                                'type' => 'number',
                                "description" => 'The ID of the parent resource to add the resource to. Ask the user to confirm the parent and use appropriate tool to find the resource ID if needed. Provide 0 to create in the top-level of the site.'
                            ],
                            'content' => [
                                'type' => 'string',
                                "description" => 'The content of the new resource. Generate this based on the users\' prompt. Allow the user to iterate on the content to create before finally calling the appropriate tool. Generate the content as HTML.'
                            ],
                        ],
                        "required" => ["name", "description", 'content']
                    ],
                    "description" => "List of resources to create"
                ],
            ],
            "required" => ["resources"]
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

        foreach ($parameters['resources'] as $data) {
            if (!isset($data['parent'])) {
                return json_encode(['success' => false, 'message' => 'parent is required.']);
            }

            if (empty($data['template'])) {
                return json_encode(['success' => false, 'message' => 'template is required.']);
            }

            if (intval($data['parent']) > 0) {
                $parentResource = $this->modx->getObject(
                    \MODX\Revolution\modResource::class,
                    ['id' => $data['parent']]
                );
                if (!$parentResource) {
                    return json_encode(['success' => false, 'message' => 'Invalid parent resource ID provided.']);
                }
            }

            $templateObj = $this->modx->getObject(\MODX\Revolution\modTemplate::class, ['id' => $data['template']]);
            if (!$templateObj) {
                return json_encode(['success' => false, 'message' => 'Invalid template ID provided.']);
            }

            $resource = $this->modx->newObject(modResource::class);
            $resource->set('pagetitle', $data['pagetitle']);
            $resource->set('template', $data['template']);
            $resource->set('parent', $data['parent']);
            $resource->set('content', $data['content']);
            $resource->set('published', false);
            $resource->save();

            $output[] = [
                'id' => $resource->get('id'),
                'pagetitle' => $resource->get('pagetitle'),
                'edit_url' => $this->modx->config['manager_url'] . '?a=resource/update&id=' . $resource->get('id'),
            ];
        }


        return json_encode($output);
    }

    public static function checkPermissions(modX $modx): bool
    {
        return $modx->hasPermission('save_document');
    }

    public static function getDescription(): string
    {
        return 'Creates a new resource';
    }
}
