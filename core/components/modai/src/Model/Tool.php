<?php
namespace modAI\Model;

use modAI\Exceptions\LexiconException;
use modAI\Tools\ToolInterface;
use MODX\Revolution\modX;

/**
 * Class Tool
 *
 * @property string $name
 * @property class-string<ToolInterface> $class
 * @property array $properties
 *
 * @package modAI\Model
 */
class Tool extends \xPDO\Om\xPDOSimpleObject
{
    /**
     * @param modX $modx
     * @return array<string, Tool>
     */
    public static function getAvailableTools(modX $modx): array
    {
        $output = [];

        $tools = $modx->getIterator(self::class, ['enabled' => true]);

        foreach ($tools as $tool) {
            $output[$tool->get('name')] = $tool;
        }

        return $output;
    }

    public function getToolInstance(): ToolInterface
    {
        $className = $this->get('class');
        if (!class_exists($className)) {
            throw new LexiconException('modai.error.tool_not_available', ['class' => $className]);
        }

        if (!is_subclass_of($className, ToolInterface::class, true)) {
            throw new LexiconException('modai.error.tool_wrong_interface');
        }

        $config = $this->get('config') ?? [];
        try {
            return new $className($this->xpdo, $config);
        } catch (\Throwable $e) {
            throw new LexiconException('modai.error.tool_instance_err', ['msg' =>$e->getMessage()]);
        }
    }
}
