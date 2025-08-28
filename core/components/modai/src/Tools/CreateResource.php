<?php

namespace modAI\Tools;

use modAI\Config\ConfigBuilder;
use modAI\Config\FieldBuilder;
use modAI\Utils;
use MODX\Revolution\modResource;
use MODX\Revolution\modX;

class CreateResource implements ToolInterface
{
    private $modx;
    private bool $clearCache;

    public static function getSuggestedName(): string
    {
        return 'create_resource';
    }

    public static function getPrompt(modX $modx): string
    {
        return "Creates a new resource, or page, on the website. ALWAYS ask for explicit user confirmation in a separate message before calling this function, even if user asks directly to create a resource, ask the user to confirm the resource before calling the tool with the information you intend to use.";
    }

    public static function getParameters(modX $modx): array
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
        return ConfigBuilder::new($modx)
            ->addField('clear_cache', function (FieldBuilder $field) use ($modx) {
                return $field
                    ->name($modx->lexicon('modai.admin.tool.config.clear_cache'))
                    ->description($modx->lexicon('modai.admin.tool.config.clear_cache_desc'))
                    ->type('combo-boolean')
                    ->default(1)
                    ->build();
            })
            ->build();
    }

    public function __construct(modX $modx, array $config = [])
    {
        $this->modx = $modx;

        $this->clearCache = intval(Utils::getConfigValue($modx, 'clear_cache', $config, '1')) === 1;
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

        if (empty($arguments)) {
            return json_encode(['success' => false, 'message' => 'Parameters are required.']);
        }

        $output = [];

        $contexts = [];

        foreach ($arguments['resources'] as $data) {
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

            $contexts[$resource->get('context_key')] = true;

            $output[] = [
                'id' => $resource->get('id'),
                'pagetitle' => $resource->get('pagetitle'),
                'edit_url' => $this->modx->config['manager_url'] . '?a=resource/update&id=' . $resource->get('id'),
            ];
        }

        if ($this->clearCache && !empty($contexts)) {
            $contexts = array_keys($contexts);
            $this->modx->cacheManager->refresh([
                'db' => [],
                'auto_publish' => ['contexts' => $contexts],
                'context_settings' => ['contexts' => $contexts],
                'resource' => ['contexts' => $contexts],
            ]);
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
