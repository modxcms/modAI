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
            'name' => NULL,
            'class' => NULL,
            'config' => '{}',
            'enabled' => 0,
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
            'class' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '200',
                'phptype' => 'string',
                'null' => false,
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
    );

}
