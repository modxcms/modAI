<?php
namespace modAI\Processors\PromptLibrary\Categories;


use modAI\Model\PromptLibraryCategory;
use MODX\Revolution\Processors\ModelProcessor;

class GetNodes extends ModelProcessor
{
    public $classKey = PromptLibraryCategory::class;
    public $objectType = 'modai.admin.prompt_library.category';
    public $permission = 'modai_admin_prompt_library';
    public $languageTopics = ['modai:default'];

    /** @var array Current node */
    public $node;
    /** @var array Permissions user has */
    public $has;


    public function initialize()
    {
        $id = $this->getProperty($this->primaryKeyField, 0);
        $id = (substr($id, 0, 2) == 'n_') ? substr($id, 2) : $id;
        $this->node = explode('_', $id);

        /* check permissions */
        $this->has = [
            'save' => $this->modx->hasPermission('modai_admin_prompt_library_category_save'),
            'remove' => $this->modx->hasPermission('modai_admin_prompt_library_category_delete'),
        ];

        return true;
    }

    public function getRootNodes()
    {
        $list = [];

        $c = $this->modx->newQuery($this->classKey);
        $c->where(['parent_id' => 0]);
        $c->sortby('rank', 'ASC');
        $categories = $this->modx->getIterator($this->classKey, $c);

        /** @var PromptLibraryCategory[] $categories */
        foreach ($categories as $category) {
            $setArray = [
                'text' => $this->modx->lexicon('modai.admin.prompt_library.category.' . $category->get('type')),
                'id' => 'cat_' . $category->get('id'),
                'leaf' => false,
                'cls' => 'tree-pseudoroot-node',
                'iconCls' => 'icon ' . ($category->get('type') === 'text' ? 'icon-file-alt' : 'icon-image'),
                'href' => '',
                'type' => $category->get('type'),
                'data' => $category->toArray(),
                'draggable' => false,
                'pseudoroot' => true,
            ];
            $list[] = $setArray;
        }

        return $list;
    }

    public function getCategoryNode($parentId)
    {
        $list = [];

        $c = $this->modx->newQuery($this->classKey);
        $c->where(['parent_id' => $parentId]);
        $c->where([
            'public' => true,
            'OR:created_by:=' => $this->modx->user->id,
        ]);
        $c->sortby('rank', 'ASC');
        $categories = $this->modx->getIterator($this->classKey, $c);

        /** @var PromptLibraryCategory[] $categories */
        foreach ($categories as $category) {
            $menu = [];
            if ($this->has['save']) {
                $menu[] = [
                    'text' => $this->modx->lexicon($this->objectType . '.create_child'),
                    'cat_id' => $category->get('id'),
                    'handler' => 'function(itm,e) {
                        this.createCategory(itm,e);
                    }',
                ];
                $menu[] = '-';
                $menu[] = [
                    'text' => $this->modx->lexicon($this->objectType . '.update'),
                    'cat_id' => $category->get('id'),
                    'handler' => 'function(itm,e) {
                        this.updateCategory(itm,e);
                    }',
                ];
            }
            if ($this->has['remove']) {
                $menu[] = '-';
                $menu[] = [
                    'text' => $this->modx->lexicon($this->objectType . '.remove'),
                    'cat_id' => $category->get('id'),
                    'handler' => 'function(itm,e) {
                        this.removeCategory(itm,e);
                    }',
                ];
            }

            $enabled = $category->get('enabled');
            $public = $category->get('public');

            $setArray = [
                'text' => $category->get('name'),
                'id' => 'cat_' . $category->get('id'),
                'leaf' => false,
                'cls' => !$enabled ? 'modai-admin--prompt-library_category_disabled' : '',
                'iconCls' => 'icon ' . ($enabled ? ($public ? 'icon-globe' : 'icon-user') : 'icon-eye-slash'),
                'href' => '',
                'data' => $category->toArray(),
                'menu' => ['items' => $menu],
            ];
            $list[] = $setArray;
        }

        return $list;
    }


    public function getMap()
    {
        $id = $this->getProperty('id');
        $id = empty($id) ? 0 : (substr($id, 0, 2) == 'n_' ? substr($id, 2) : $id);

        return explode('_', $id);
    }

    public function process()
    {
        $map = $this->getMap();
        switch ($map[0]) {
            case 'root':
                $list = $this->getRootNodes();
                break;
            case 'cat':
                $list = isset($this->node[1]) ? $this->getCategoryNode($this->node[1]) : [];
                break;
            default:
                $list = [];
                break;
        }

        return $this->toJSON($list);
    }
}
