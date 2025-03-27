<?php
namespace modAI\Model\mysql;

use xPDO\xPDO;

class AgentContextProvider extends \modAI\Model\AgentContextProvider
{

    public static $metaMap = array (
        'package' => 'modAI\\Model\\',
        'version' => '3.0',
        'table' => 'modai_agent_context_provider',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'agent_id' => 0,
            'context_provider_id' => 0,
        ),
        'fieldMeta' => 
        array (
            'agent_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'int',
                'null' => false,
                'default' => 0,
                'attributes' => 'unsigned',
            ),
            'context_provider_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'int',
                'null' => false,
                'default' => 0,
                'attributes' => 'unsigned',
            ),
        ),
        'indexes' => 
        array (
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
