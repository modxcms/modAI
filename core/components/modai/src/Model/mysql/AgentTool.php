<?php
namespace modAI\Model\mysql;

use xPDO\xPDO;

class AgentTool extends \modAI\Model\AgentTool
{

    public static $metaMap = array (
        'package' => 'modAI\\Model\\',
        'version' => '3.0',
        'table' => 'modai_agent_tools',
        'extends' => 'xPDO\\Om\\xPDOObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'agent_id' => NULL,
            'tool_id' => NULL,
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
            'tool_id' => 
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
                    'tool_id' => 
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
            'tool_id' => 
            array (
                'alias' => 'tool_id',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'tool_id' => 
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
            'Tool' => 
            array (
                'cardinality' => 'one',
                'class' => 'modAI\\Model\\Tool',
                'foreign' => 'id',
                'local' => 'tool_id',
                'owner' => 'foreign',
            ),
        ),
    );

}
