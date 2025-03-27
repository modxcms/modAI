<?php
namespace modAI\Model\mysql;

use xPDO\xPDO;

class AgentTool extends \modAI\Model\AgentTool
{

    public static $metaMap = array (
        'package' => 'modAI\\Model\\',
        'version' => '3.0',
        'table' => 'modai_agent_tool',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'agent_id' => 0,
            'tool_id' => 0,
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
            'tool_id' => 
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
