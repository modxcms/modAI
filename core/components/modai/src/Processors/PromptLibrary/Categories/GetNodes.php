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
        $nodes = [];


        $nodes[] = [
            'text' => $this->modx->lexicon('modai.admin.prompt_library.category.text'),
            'id' => 'n_type_text',
            'leaf' => false,
            'cls' => 'tree-pseudoroot-node',
            'iconCls' => 'icon icon-file-alt',
            'page' => '',
            'classKey' => 'root',
            'type' => 'text',
            'draggable' => false,
            'pseudoroot' => true,
        ];

        $nodes[] = [
            'text' => $this->modx->lexicon('modai.admin.prompt_library.category.image'),
            'id' => 'n_type_image',
            'leaf' => false,
            'cls' => 'tree-pseudoroot-node',
            'iconCls' => 'icon icon-image',
            'page' => '',
            'classKey' => 'root',
            'type' => 'image',
            'draggable' => false,
            'pseudoroot' => true,
        ];


        return $nodes;
    }

    public function getCategoryNode($parentId)
    {
        $list = [];

        $c = $this->modx->newQuery($this->classKey);
        $c->where(['parent_id' => $parentId]);
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

            $setArray = [
                'text' => $category->get('name'),
                'id' => 'cat_' . $category->get('id'),
                'leaf' => false,
                'cls' => !$enabled ? 'modai-admin--prompt-library_category_disabled' : '',
                'iconCls' => $enabled ? 'icon icon-folder' : 'icon icon-eye-slash',
                'href' => '',
                'data' => $category->toArray(),
                'menu' => ['items' => $menu],
            ];
            $list[] = $setArray;
        }

        return $list;
    }

    public function getCategoriesForType($type)
    {
        $list = [];

        $c = $this->modx->newQuery($this->classKey);
        $c->where(['parent_id' => 0, 'type' => $type]);
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

            $setArray = [
                'text' => $category->get('name'),
                'id' => 'cat_' . $category->get('id'),
                'leaf' => false,
                'cls' => !$enabled ? 'modai-admin--prompt-library_category_disabled' : '',
                'iconCls' => $enabled ? 'icon icon-folder' : 'icon icon-eye-slash',
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
//                $list = $this->getCategoryNode(0);
                break;
            case 'type':
                $list = $this->getCategoriesForType($map[1]);
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
