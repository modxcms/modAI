<?php
namespace modAI\Model\mysql;

use xPDO\xPDO;

class AgentContextProvider extends \modAI\Model\AgentContextProvider
{

    public static $metaMap = array (
        'package' => 'modAI\\Model\\',
        'version' => '3.0',
        'table' => 'modai_agent_context_providers',
        'extends' => 'xPDO\\Om\\xPDOObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'agent_id' => NULL,
            'context_provider_id' => NULL,
        ),
        'fieldMeta' => 
        array (
            'agent_id' => 
            array (
                'dbtype' => 'int',
                'attributes' => 'unsigned',
                'precision' => '10',
                'phptype' => 'int',
                'null' => false,
                'index' => 'pk',
            ),
            'context_provider_id' => 
            array (
                'dbtype' => 'int',
                'attributes' => 'unsigned',
                'precision' => '10',
                'phptype' => 'int',
                'null' => false,
                'index' => 'pk',
            ),
        ),
        'indexes' => 
        array (
            'PRIMARY' => 
            array (
                'alias' => 'PRIMARY',
                'primary' => true,
                'unique' => true,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'agent_id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'context_provider_id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'agent_id' => 
            array (
                'alias' => 'agent_id',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'agent_id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'context_provider_id' => 
            array (
                'alias' => 'context_provider_id',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'context_provider_id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
        ),
        'aggregates' => 
        array (
            'Agent' => 
            array (
                'cardinality' => 'one',
                'class' => 'modAI\\Model\\Agent',
                'foreign' => 'id',
                'local' => 'agent_id',
                'owner' => 'foreign',
            ),
            'ContextProvider' => 
            array (
                'cardinality' => 'one',
                'class' => 'modAI\\Model\\ContextProvider',
                'foreign' => 'id',
                'local' => 'context_provider_id',
                'owner' => 'foreign',
            ),
        ),
    );

}
