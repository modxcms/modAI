<?php
namespace modAI\Processors\PromptLibrary\Categories;


use modAI\Model\PromptLibraryCategory;
use MODX\Revolution\Processors\Processor;

class Sort extends Processor
{
    public $nodes = [];
    public $nodesAffected = [];
    public $source;
    public $target;
    public $menuindex;
    public $point;

    public function checkPermissions()
    {
        return $this->modx->hasPermission('modai_admin_prompt_library_category_save');
    }

    public function getLanguageTopics()
    {
        return ['modai:default'];
    }

    public function process()
    {
        $target = $this->getProperty('target', '');
        $source = $this->getProperty('source', '');
        $point = $this->getProperty('point', '');

        if (empty($target)) {
            return $this->failure('Target not set');
        }

        if (empty($source)) {
            return $this->failure('Source not set');
        }

        if (empty($point)) {
            return $this->failure('Point not set');
        }

        $this->point = $point;
        $this->parseNodes($source, $target);

        $sorted = $this->sort();
        if ($sorted !== true) {
            return $this->failure($sorted);
        }

        return $this->success();
    }

    public function parseNodes($source, $target)
    {
        $source = explode('_', $source);
        $target = explode('_', $target);

        if (intval($source[1]) == 0) {
            $this->source = null;
        } else {
            $this->source = $this->modx->getObject(PromptLibraryCategory::class, $source[1]);
        }

        if (intval($target[1]) == 0) {
            $this->target = null;
        } else {
            $this->target = $this->modx->getObject(PromptLibraryCategory::class, $target[1]);
        }
    }

    public function sort()
    {
        if (($this->source === null) || ($this->target === null)) {
            return true;
        }

        return $this->sortCategories();
    }

    public function sortCategories()
    {
        $lastRank = $this->target->rank;

        if ($this->point == 'above') {
            return $this->moveCategoryAbove($lastRank);
        }

        if ($this->point == 'below') {
            return $this->moveCategoryBelow($lastRank);
        }

        if ($this->point == 'append') {
            return $this->appendCategory();
        }

        return false;
    }

    public function moveCategoryAbove($lastRank)
    {
        $moved = $this->moveCategory('source', $lastRank);
        if ($moved !== true) {
            return $moved;
        }

        $moved = $this->moveCategory('target', $lastRank + 1);
        if ($moved !== true) {
            return $moved;
        }

        return $this->moveAffectedCategories($lastRank);
    }

    public function moveCategoryBelow($lastRank)
    {
        $this->moveCategory('source', $lastRank + 1);

        return $this->moveAffectedCategories($lastRank);
    }

    public function appendCategory()
    {
        $c = $this->modx->newQuery(PromptLibraryCategory::class);
        $c->where([
            'parent_id' => $this->target->id,
        ]);
        $c->sortby('rank', 'DESC');
        $c->limit(1);

        /** @var PromptLibraryCategory $lastResource */
        $lastResource = $this->modx->getObject(PromptLibraryCategory::class, $c);

        if ($lastResource) {
            $this->source->set('rank', $lastResource->rank + 1);
        } else {
            $this->source->set('rank', 0);
        }

        $this->source->set('parent_id', $this->target->id);

        $this->source->save();
        $this->nodesAffected[] = $this->source;

        return true;
    }

    public function moveCategory($type, $rank)
    {
        $this->$type->set('rank', $rank);
        $this->$type->set('parent_id', $this->target->parent_id);

        $this->$type->save();
        $this->nodesAffected[] = $this->$type;

        return true;
    }

    public function moveAffectedCategories($lastRank)
    {
        $c = $this->modx->newQuery(PromptLibraryCategory::class);
        $c->where([
            'id:NOT IN' => [$this->source->id, $this->target->id],
            'rank:>=' => $lastRank,
            'parent_id' => $this->target->parent_id,
        ]);
        $c->sortby('rank', 'ASC');

        $resourcesToSort = $this->modx->getIterator(PromptLibraryCategory::class, $c);
        $lastRank = $lastRank + 2;

        /** @var PromptLibraryCategory $resource */
        foreach ($resourcesToSort as $resource) {
            $resource->set('rank', $lastRank);

            $resource->save();
            $this->nodesAffected[] = $resource;
            $lastRank++;
        }

        return true;
    }
}
