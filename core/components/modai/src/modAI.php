<?php

namespace modAI;

use MODX\Revolution\modTemplateVar;
use MODX\Revolution\modX;

class modAI
{
    /** @var \MODX\Revolution\modX $modx */
    public $modx;

    public $namespace = 'modai';

    /** @var array $config */
    public $config = [];

    private $lit = null;

    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;

        if (!$this->hasAccess()) {
            throw new \Exception('Unauthorized');
        }

        $corePath = $this->getOption('core_path', $config, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/modai/');
        $assetsUrl = $this->getOption('assets_url', $config, $this->modx->getOption('assets_url', null, MODX_ASSETS_URL) . 'components/modai/');

        $this->config = array_merge(
            [
                'corePath'  => $corePath,
                'srcPath'   => $corePath . 'src/',
                'modelPath' => $corePath . 'src/Model/',
                'assetsUrl' => $assetsUrl,

                'mgrCssUrl'    => $assetsUrl . 'mgr/css/',
                'mgrJsUrl'     => $assetsUrl . 'mgr/js/',

                'templatesPath' => $corePath . 'templates/',
            ],
            $config
        );
    }

    /**
     * Get a local configuration option or a namespaced system setting by key.
     *
     * @param  string  $key  The option key to search for.
     * @param  array  $options  An array of options that override local options.
     * @param  mixed  $default  The default value returned if the option is not found locally or as a
     * namespaced system setting; by default this value is null.
     *
     * @return mixed The option value or the default value specified.
     */
    public function getOption(string $key, $options = [], $default = null)
    {
        if (empty($key) || !is_string($key)) {
            return $default;
        }

        if (!empty($options) && array_key_exists($key, $options)) {
            return $options[$key];
        }

        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        if (array_key_exists("{$this->namespace}.{$key}", $this->modx->config)) {
            return $this->modx->getOption("{$this->namespace}.{$key}");
        }

        return $default;
    }

    public function getListOfTVs()
    {
        $tvs = $this->modx->getOption('modai.tvs');
        if (empty($tvs)) {
            return [];
        }

        $tvs = explode(',', $tvs);
        $tvs = array_map('trim', $tvs);
        $tvs = array_keys(array_flip($tvs));

        return array_filter($tvs);
    }

    public function getResourceFields()
    {
        $fields = $this->modx->getOption('modai.res.fields');
        if (empty($fields)) {
            return [];
        }

        $fields = explode(',', $fields);
        $fields = array_map('trim', $fields);
        $fields = array_keys(array_flip($fields));

        return array_filter($fields);
    }

    public function getListOfTVsWithIDs()
    {
        $tvs = $this->getListOfTVs();
        if (empty($tvs)) {
            return [];
        }

        $output = [];

        $tvObjects = $this->modx->getIterator(modTemplateVar::class, ['name:IN' => $tvs]);
        foreach ($tvObjects as $tvObject) {
            $output[] = [$tvObject->get('id'), $tvObject->get('name')];
        }

        return $output;
    }

    public function getLit()
    {
        if ($this->lit !== null) {
            return $this->lit;
        }

        $this->lit = (int)$this->modx->getOption('modai.cache.lit', null, '0');
        return $this->lit;
    }

    public function getAPIUrl()
    {
        return $this->config['assetsUrl'] . 'api.php';
    }

    public function getJSFile()
    {
        $lit = $this->getLit();
        $assetsUrl = $this->getOption('assetsUrl');

        return "{$assetsUrl}js/modai.js?lit=$lit";
    }

    public function getCSSFile()
    {
        $lit = $this->getLit();
        $assetsUrl = $this->getOption('assetsUrl');

        return "{$assetsUrl}css/modai.css?lit=$lit";
    }

    public function getBaseConfig()
    {
        $firstName = explode(' ', $this->modx->user->Profile->fullname)[0];

        return [
            'name' => $firstName,
            'assetsURL' => $this->getOption('assetsUrl'),
            'apiURL' => $this->getAPIUrl(),
            'cssURL' => $this->getCSSFile(),
            'availableAgents' => $this->getAvailableAgents(),
        ];
    }

    public function hasAccess()
    {
        return !empty($this->modx->user) && !empty($this->modx->user->id) && $this->modx->hasPermission('frames');
    }

    public function getUILexiconTopics()
    {
        return ['modai:ui'];
    }

    public function getLexiconTopics()
    {
        return ['modai:ui', 'modai:setting'];
    }

    public function getAvailableAgents()
    {
        $c = $this->modx->newQuery(\modAI\Model\Agent::class);
        $c->leftJoin(\modAI\Model\AgentContextProvider::class, 'AgentContextProviders', 'AgentContextProviders.agent_id = Agent.id');
        $c->leftJoin(\modAI\Model\ContextProvider::class, 'ContextProvider', 'AgentContextProviders.context_provider_id = ContextProvider.id AND ContextProvider.enabled = 1');
        $c->where([
            'enabled' => true,
        ]);
        $c->select($this->modx->getSelectColumns(\modAI\Model\Agent::class, 'Agent', '', ['name']));
        $c->select([
            "GROUP_CONCAT(ContextProvider.name SEPARATOR ',') AS context_providers"
        ]);
        $c->groupby('Agent.name');
        $c->prepare();
        $c->stmt->execute();

        $output = [];

        while ($row = $c->stmt->fetch(\PDO::FETCH_ASSOC)) {
            $output[$row['name']] = [
                'id' => $row['name'],
                'name' => $row['name'],
                'contextProviders' => empty($row['context_providers']) ? null : explode(',', $row['context_providers']),
            ];
        }

        return $output;
    }
}
