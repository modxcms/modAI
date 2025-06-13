<?php

namespace modAI;

use modAI\Model\PromptLibraryCategory;
use modAI\Model\PromptLibraryPrompt;
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
        $this->modx = &$modx;

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

    public function getChatAdditionalControls()
    {
        $additionalControls = $this->modx->getOption('modai.chat.additional_controls', null, '');
        try {
            return json_decode($additionalControls, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getBaseConfig()
    {
        $firstName = explode(' ', $this->modx->user->Profile->fullname)[0];

        return [
            'assetsURL' => $this->getOption('assetsUrl'),
            'apiURL' => $this->getAPIUrl(),
            'cssURL' => $this->getCSSFile(),
            'availableAgents' => $this->getAvailableAgents(),
            'promptLibrary' => $this->getPromptLibrary(),
            'permissions' => $this->getClientPermissions(),
            'chatAdditionalControls' => $this->getChatAdditionalControls(),
            'generateChatTitle' => intval($this->modx->getOption('modai.chat.title.generate', null, '1')) === 1,
            'user' => [
                'id' => $this->modx->user->id,
                'name' => $firstName,
            ]
        ];
    }

    /**
     * @param string|array|null $config
     * @return string
     */
    public function getInitCode($config = null, bool $merge = true)
    {
        if (is_string($config)) {
            if (!$merge) {
                $cfg = $config;
            } else {
                $baseConfig = $this->getBaseConfig();
                $cfg = '{...' . json_encode($baseConfig) . ', ...' . $config . '}';
            }
        } else if (is_array($config)) {
            if ($merge) {
                $baseConfig = $this->getBaseConfig();
                $config = array_merge($baseConfig, $config);
            }

            $cfg = json_encode($config);
        } else {
            $baseConfig = $this->getBaseConfig();
            $cfg = json_encode($baseConfig);
        }

        $onInits = $this->modx->invokeEvent('modAIOnInit');
        $onInits = is_array($onInits) ? implode(PHP_EOL, $onInits) : '';

        return <<< EOT
            const modAI = ModAI.init($cfg);
            $onInits
        EOT;
    }

    public function hasAccess()
    {
        return !empty($this->modx->user) && !empty($this->modx->user->id) && ($this->modx->hasPermission('modai_admin') || $this->modx->hasPermission('modai_client'));
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
        $c->select($this->modx->getSelectColumns(\modAI\Model\Agent::class, 'Agent', '', ['name', 'user_groups']));
        $c->select([
            "GROUP_CONCAT(ContextProvider.name SEPARATOR ',') AS context_providers"
        ]);
        $c->groupby('Agent.name');
        $c->prepare();
        $c->stmt->execute();

        $output = [];

        $userGroups = $this->modx->user->getUserGroups();

        while ($row = $c->stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (!$this->modx->user->sudo && $row['user_groups'] !== null) {
                $agentGroups = json_decode($row['user_groups'], true);
                $match = array_intersect($agentGroups, $userGroups);

                if (count($match) === 0) {
                    continue;
                }
            }

            $output[$row['name']] = [
                'id' => $row['name'],
                'name' => $row['name'],
                'contextProviders' => empty($row['context_providers']) ? null : explode(',', $row['context_providers']),
            ];
        }

        return $output;
    }

    public function getPromptLibrary()
    {
        $output = [];

        $c = $this->modx->newQuery(PromptLibraryCategory::class);
        $c->where([
            'enabled' => true,
            [
                'public' => true,
                'OR:created_by:=' => $this->modx->user->id,
                [
                    'OR:parent_id:=' => 0,
                    'created_by' => 0,
                ]
            ]
        ]);
        $c->sortby('type', 'asc');
        $c->sortby('parent_id', 'asc');
        $c->sortby('rank', 'asc');

        $categories = $this->modx->getIterator(PromptLibraryCategory::class, $c);

        $catById = [];
        $allCategoryIDs = [];
        foreach ($categories as $category) {
            if (!isset($output[$category->type])) {
                $output[$category->type] = [];
            }

            $catById[$category->id] = [
                'id' => 'cat_' . $category->id,
                'name' => $category->name,
                'children' => []
            ];

            $allCategoryIDs[] = $category->id;

            $catItem = &$catById[$category->id];

            if (empty($category->parent_id)) {
                $output[$category->type][] = &$catItem;
            } else {
                $catById[$category->parent_id]['children'][] = &$catItem;
            }
        }

        if (empty($allCategoryIDs)) {
            return [];
        }

        $pc = $this->modx->newQuery(PromptLibraryPrompt::class);
        $pc->where([
            'enabled' => true,
            'category_id:IN' => $allCategoryIDs,
            [
                'public' => true,
                'OR:created_by:=' => $this->modx->user->id,
            ]
        ]);
        $pc->sortby('category_id', 'asc');
        $pc->sortby('rank', 'asc');

        $prompts = $this->modx->getIterator(PromptLibraryPrompt::class, $pc);

        foreach ($prompts as $prompt) {
            $catById[$prompt->category_id]['children'][] = [
                'id' => 'prompt_' . $prompt->id,
                'name' => $prompt->name,
                'value' => $prompt->prompt,
            ];
        }

        $removeLeafNodes = function (array &$nodes) use (&$removeLeafNodes) {
            foreach ($nodes as $key => &$node) {
                if (!empty($node['children'])) {
                    // Recursively clean children first
                    $removeLeafNodes($node['children']);
                }

                // After recursion, if children are still empty, remove this node
                if (isset($node['children']) && empty($node['children'])) {
                    unset($nodes[$key]);
                }
            }

            // Reindex array to maintain sequential keys
            $nodes = array_values($nodes);
        };

        foreach ($output as &$treeByType) {
            $removeLeafNodes($treeByType);
        }

        if (isset($output['text']) && count($output['text']) > 0) {
            $output['text'] = $output['text'][0]['children'];
        }

        if (isset($output['image']) && count($output['image']) > 0) {
            $output['image'] = $output['image'][0]['children'];
        }

        return $output;
    }

    public function getAdminPermissions()
    {
        return [
            'modai_admin' => (int)$this->modx->hasPermission('modai_admin'),
            'modai_admin_tools' => (int)$this->modx->hasPermission('modai_admin_tools'),
            'modai_admin_tool_save' => (int)$this->modx->hasPermission('modai_admin_tool_save'),
            'modai_admin_tool_delete' => (int)$this->modx->hasPermission('modai_admin_tool_delete'),
            'modai_admin_context_providers' => (int)$this->modx->hasPermission('modai_admin_context_providers'),
            'modai_admin_context_provider_save' => (int)$this->modx->hasPermission('modai_admin_context_provider_save'),
            'modai_admin_context_provider_delete' => (int)$this->modx->hasPermission('modai_admin_context_provider_delete'),
            'modai_admin_agents' => (int)$this->modx->hasPermission('modai_admin_agents'),
            'modai_admin_agent_save' => (int)$this->modx->hasPermission('modai_admin_agent_save'),
            'modai_admin_agent_delete' => (int)$this->modx->hasPermission('modai_admin_agent_delete'),
            'modai_admin_agent_tool_save' => (int)$this->modx->hasPermission('modai_admin_agent_tool_save'),
            'modai_admin_agent_tool_delete' => (int)$this->modx->hasPermission('modai_admin_agent_tool_delete'),
            'modai_admin_agent_context_provider_save' => (int)$this->modx->hasPermission('modai_admin_agent_context_provider_save'),
            'modai_admin_agent_context_provider_delete' => (int)$this->modx->hasPermission('modai_admin_agent_context_provider_delete'),
            'modai_admin_related_agent_save' => (int)$this->modx->hasPermission('modai_admin_related_agent_save'),
            'modai_admin_related_agent_delete' => (int)$this->modx->hasPermission('modai_admin_related_agent_delete'),
            'modai_admin_prompt_library' => (int)$this->modx->hasPermission('modai_admin_prompt_library'),
            'modai_admin_prompt_library_prompt_save' => (int)$this->modx->hasPermission('modai_admin_prompt_library_prompt_save'),
            'modai_admin_prompt_library_prompt_save_public' => (int)$this->modx->hasPermission('modai_admin_prompt_library_prompt_save_public'),
            'modai_admin_prompt_library_prompt_delete' => (int)$this->modx->hasPermission('modai_admin_prompt_library_prompt_delete'),
            'modai_admin_prompt_library_category_save' => (int)$this->modx->hasPermission('modai_admin_prompt_library_category_save'),
            'modai_admin_prompt_library_category_save_public' => (int)$this->modx->hasPermission('modai_admin_prompt_library_category_save_public'),
            'modai_admin_prompt_library_category_delete' => (int)$this->modx->hasPermission('modai_admin_prompt_library_category_delete'),
        ];
    }

    public function getClientPermissions()
    {
        return [
            'modai_client' => (int)$this->modx->hasPermission('modai_client'),
            'modai_client_chat_text' => (int)$this->modx->hasPermission('modai_client_chat_text'),
            'modai_client_chat_image' => (int)$this->modx->hasPermission('modai_client_chat_image'),
            'modai_client_text' => (int)$this->modx->hasPermission('modai_client_text'),
            'modai_client_vision' => (int)$this->modx->hasPermission('modai_client_vision'),
        ];
    }
}
