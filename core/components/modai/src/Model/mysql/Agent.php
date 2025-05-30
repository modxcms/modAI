<?php
namespace modAI\Model\mysql;

use xPDO\xPDO;

class Agent extends \modAI\Model\Agent
{

    public static $metaMap = array (
        'package' => 'modAI\\Model\\',
        'version' => '3.0',
        'table' => 'modai_agents',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'name' => NULL,
            'description' => '',
            'prompt' => '',
            'model' => '',
            'enabled' => 0,
            'advanced_config' => NULL,
            'user_groups' => NULL,
        ),
        'fieldMeta' => 
        array (
            'name' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '200',
                'phptype' => 'string',
                'null' => false,
            ),
            'description' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '500',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'prompt' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'model' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '200',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'enabled' => 
            array (
                'dbtype' => 'tinyint',
                'precision' => '1',
                'phptype' => 'boolean',
                'null' => false,
                'default' => 0,
            ),
            'advanced_config' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'json',
                'null' => true,
            ),
            'user_groups' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'json',
                'null' => true,
            ),
        ),
        'indexes' => 
        array (
            'enabled' => 
            array (
                'alias' => 'enabled',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'enabled' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'name' => 
            array (
                'alias' => 'name',
                'primary' => false,
                'unique' => true,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'name' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
        ),
        'composites' => 
        array (
            'AgentTools' => 
            array (
                'cardinality' => 'many',
                'class' => 'modAI\\Model\\AgentTool',
                'foreign' => 'agent_id',
                'local' => 'id',
                'owner' => 'local',
            ),
            'AgentContextProviders' => 
            array (
                'cardinality' => 'many',
                'class' => 'modAI\\Model\\AgentContextProvider',
                'foreign' => 'agent_id',
                'local' => 'id',
                'owner' => 'local',
            ),
        ),
    );

}
