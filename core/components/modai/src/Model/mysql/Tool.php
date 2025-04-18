<?php
namespace modAI\Model\mysql;

use xPDO\xPDO;

class Tool extends \modAI\Model\Tool
{

    public static $metaMap = array (
        'package' => 'modAI\\Model\\',
        'version' => '3.0',
        'table' => 'modai_tools',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'class' => NULL,
            'name' => NULL,
            'description' => '',
            'prompt' => NULL,
            'config' => NULL,
            'enabled' => 0,
            'default' => 0,
        ),
        'fieldMeta' => 
        array (
            'class' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '200',
                'phptype' => 'string',
                'null' => false,
            ),
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
                'null' => true,
                'default' => '',
            ),
            'prompt' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'string',
                'null' => true,
            ),
            'config' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'json',
                'null' => true,
            ),
            'enabled' => 
            array (
                'dbtype' => 'tinyint',
                'precision' => '1',
                'phptype' => 'boolean',
                'null' => false,
                'default' => 0,
            ),
            'default' => 
            array (
                'dbtype' => 'tinyint',
                'precision' => '1',
                'phptype' => 'boolean',
                'null' => false,
                'default' => 0,
            ),
        ),
        'indexes' => 
        array (
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
            'class' => 
            array (
                'alias' => 'class',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'class' => 
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
                'foreign' => 'tool_id',
                'local' => 'id',
                'owner' => 'local',
            ),
        ),
    );

}
