<?php

namespace modAI\Tools;

use modAI\Config\ConfigBuilder;
use modAI\Config\FieldBuilder;
use modAI\Utils;
use MODX\Revolution\modResource;
use MODX\Revolution\modX;

class EditResource implements ToolInterface
{
    private $modx;
    private bool $clearCache;

    public static function getSuggestedName(): string
    {
        return 'edit_resource';
    }

    public static function getPrompt(modX $modx): string
    {
        return "Edits an existing resource or page, on the website. Use when explicitly asked to update/edit a resource or a page. ALWAYS ask for explicit user confirmation in a separate message before calling this function, even if user asks directly to edit/update a resource. Use appropriate tools to get the current data for specific resource, or to find resource ID if needed.";
    }

    public static function getParameters(modX $modx): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'number',
                    "description" => 'Id of the resource to edit'
                ],
                'raw_content' => [
                    'type' => 'string',
                    "description" => 'The updated content of the resource. Respect the html/markdown format and don\'t modify Revolution tags (They are in double square brackets). Use appropriate tool to get the current raw_content and allow the user to iterate on it before finally calling this tool. Make sure you are using raw_content from the appropriate tool and not content.'
                ]
            ],
            "required" => ['id'],
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

        $resource = $this->modx->getObject(modResource::class, ['id' => $arguments['id']]);
        if (!$resource) {
            return json_encode(['success' => false, 'message' => 'Invalid resource ID provided.']);
        }
        $resource->set('content', (string)$arguments['raw_content']);
        if (!$resource->save()) {
            return json_encode(['success' => false, 'message' => 'Could not save resource.']);
        }

        if ($this->clearCache) {
            $contexts = [$resource->get('context_key')];
            $this->modx->cacheManager->refresh([
                'db' => [],
                'auto_publish' => ['contexts' => $contexts],
                'context_settings' => ['contexts' => $contexts],
                'resource' => ['contexts' => $contexts],
            ]);
        }

        return json_encode(['success' => true, 'message' => 'Resource updated.']);
    }

    public static function checkPermissions(modX $modx): bool
    {
        return $modx->hasPermission('save_document');
    }

    public static function getDescription(): string
    {
        return 'Allows the assistant to edit resources.';
    }
}
