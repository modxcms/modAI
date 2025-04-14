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
    public static function getAvailableTools(modX $modx, ?int $agentId = null): array
    {
        $c = $modx->newQuery(self::class);

        $where = [
            [
                'enabled' => true,
                'default' => true,
            ]
        ];

        if (!empty($agentId)) {
            $agentToolsCriteria = $modx->newQuery(AgentTool::class, ['agent_id' => $agentId]);
            $agentToolsCriteria->select('tool_id');
            $agentToolsCriteria->prepare();
            $agentToolsCriteria->stmt->execute();

            $agentTools = $agentToolsCriteria->stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
            $agentTools = array_map('intval', $agentTools);

            if (!empty($agentTools)) {
                $where[] = [
                    'OR:id:IN' => $agentTools,
                    'enabled' => true,
                ];
            }
        }

        $output = [];

        $c->where($where);

        $tools = $modx->getIterator(self::class, $c);

        foreach ($tools as $tool) {
            $className = $tool->get('class');
            if (!class_exists($className)) {
                continue;
            }

            if (!is_subclass_of($className, ToolInterface::class, true)) {
                continue;
            }

            $hasPermissions = $className::checkPermissions($modx);
            if (!$hasPermissions) {
                continue;
            }

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
