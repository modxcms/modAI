<?php
namespace modAI\Model\mysql;

use xPDO\xPDO;

class ContextProvider extends \modAI\Model\ContextProvider
{

    public static $metaMap = array (
        'package' => 'modAI\\Model\\',
        'version' => '3.0',
        'table' => 'modai_context_providers',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'class' => NULL,
            'name' => NULL,
            'description' => '',
            'config' => '{}',
            'enabled' => 0,
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
            'config' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'json',
                'null' => false,
                'default' => '{}',
            ),
            'enabled' => 
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
            'AgentContextProviders' => 
            array (
                'cardinality' => 'many',
                'class' => 'modAI\\Model\\AgentContextProvider',
                'foreign' => 'context_provider_id',
                'local' => 'id',
                'owner' => 'local',
            ),
        ),
    );

}
