<?php
namespace modAI\Processors\Combos;

use modAI\Utils;
use MODX\Revolution\Processors\Model\GetListProcessor;
use MODX\Revolution\modUserGroup;
use xPDO\Om\xPDOQuery;

class UserGroups extends GetListProcessor
{
    public $classKey = modUserGroup::class;
    public $languageTopics = ['user', 'access', 'messages'];

    /**
     * @param xPDOQuery $c
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $valuesqry = $this->getProperty('valuesqry', false);
        if ($valuesqry) {
            $query = $this->getProperty('query', '');
            $query = Utils::explodeAndClean($query);
            if (!empty($query)) {
                $c->where([
                    'id:IN' => $query
                ]);
            }
        } else {
            $query = $this->getProperty('query', '');
            if (!empty($query)) {
                $c->where([
                    'name:LIKE' => '%' . $query . '%',
                    'OR:description:LIKE' => '%' . $query . '%',
                ]);
            }
        }

        return $c;
    }
}
