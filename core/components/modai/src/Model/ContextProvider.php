<?php
namespace modAI\Model;

use modAI\ContextProviders\ContextProviderInterface;
use modAI\Exceptions\InvalidContextProviderConfig;
use modAI\Exceptions\LexiconException;
use MODX\Revolution\modX;

/**
 * Class ContextProvider
 *
 * @property class-string<ContextProviderInterface> $class
 * @property string $name
 * @property string $description
 * @property array $config
 * @property boolean $enabled
 *
 * @property \modAI\Model\AgentContextProvider[] $AgentContextProviders
 *
 * @package modAI\Model
 */
class ContextProvider extends \xPDO\Om\xPDOSimpleObject
{
    /**
     * @param modX $modx
     * @return array<string, ContextProvider>
     */
    public static function getAvailableContextProviders(modX $modx, ?int $agentId = null): array
    {
        if (empty($agentId)) {
            return [];
        }

        $c = $modx->newQuery(self::class);

        $where = [];

        $agentContextProvidersCriteria = $modx->newQuery(AgentContextProvider::class, ['agent_id' => $agentId]);
        $agentContextProvidersCriteria->select('context_provider_id');
        $agentContextProvidersCriteria->prepare();
        $agentContextProvidersCriteria->stmt->execute();

        $agentContextProviders = $agentContextProvidersCriteria->stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
        $agentContextProviders = array_map('intval', $agentContextProviders);

        if (empty($agentContextProviders)) {
            return [];
        }

        $where[] = [
            'OR:id:IN' => $agentContextProviders,
            'enabled' => true,
        ];


        $output = [];

        $c->where($where);

        $contextProviders = $modx->getIterator(self::class, $c);

        foreach ($contextProviders as $contextProvider) {
            $output[$contextProvider->get('name')] = $contextProvider;
        }

        return $output;
    }

    public function getContextProviderInstance(): ContextProviderInterface
    {
        $className = $this->get('class');
        if (!class_exists($className)) {
            throw new LexiconException('modai.error.context_provider_not_available', ['class' => $className]);
        }

        if (!is_subclass_of($className, ContextProviderInterface::class, true)) {
            throw new LexiconException('modai.error.context_provider_wrong_interface');
        }

        $config = $this->get('config') ?? [];
        try {
            return new $className($this->xpdo, $config);
        } catch (InvalidContextProviderConfig $e){
            throw new LexiconException('modai.error.invalid_context_provider_config', ['class' => $this->get('class'), 'name' => $this->get('name')]);
        } catch (\Throwable $e) {
            throw new LexiconException('modai.error.context_provider_instance_err', ['msg' =>$e->getMessage()]);
        }
    }
}
