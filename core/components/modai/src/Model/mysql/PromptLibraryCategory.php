<?php
namespace modAI\Model\mysql;

use xPDO\xPDO;

class PromptLibraryCategory extends \modAI\Model\PromptLibraryCategory
{

    public static $metaMap = array (
        'package' => 'modAI\\Model\\',
        'version' => '3.0',
        'table' => 'modai_prompt_library_categories',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'name' => NULL,
            'type' => NULL,
            'enabled' => 1,
            'public' => 0,
            'rank' => 0,
            'parent_id' => 0,
            'created_by' => 0,
        ),
        'fieldMeta' => 
        array (
            'name' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '191',
                'phptype' => 'string',
                'null' => false,
            ),
            'type' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '64',
                'phptype' => 'string',
                'null' => false,
            ),
            'enabled' => 
            array (
                'dbtype' => 'tinyint',
                'precision' => '1',
                'phptype' => 'boolean',
                'null' => false,
                'default' => 1,
            ),
            'public' => 
            array (
                'dbtype' => 'tinyint',
                'precision' => '1',
                'phptype' => 'boolean',
                'null' => false,
                'default' => 0,
            ),
            'rank' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'int',
                'null' => false,
                'default' => 0,
            ),
            'parent_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'int',
                'null' => false,
                'default' => 0,
            ),
            'created_by' => 
            array (
                'dbtype' => 'int',
                'precision' => '11',
                'phptype' => 'int',
                'null' => false,
                'default' => 0,
            ),
        ),
        'indexes' => 
        array (
            'parent_id' => 
            array (
                'alias' => 'parent_id',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'parent_id' => 
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
            'rank' => 
            array (
                'alias' => 'rank',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'rank' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'type' => 
            array (
                'alias' => 'type',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'type' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'type_nabled_rank' => 
            array (
                'alias' => 'type_nabled_rank',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'type' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'enabled' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'rank' => 
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
            'Prompts' => 
            array (
                'cardinality' => 'many',
                'class' => 'modAI\\Model\\PromptLibraryPrompt',
                'foreign' => 'category_id',
                'local' => 'id',
                'owner' => 'local',
            ),
            'Children' => 
            array (
                'cardinality' => 'many',
                'class' => 'modAI\\Model\\PromptLibraryCategory',
                'foreign' => 'parent_id',
                'local' => 'id',
                'owner' => 'local',
            ),
        ),
        'aggregates' => 
        array (
            'Parent' => 
            array (
                'cardinality' => 'one',
                'class' => 'modAI\\Model\\PromptLibraryCategory',
                'foreign' => 'id',
                'local' => 'category_id',
                'owner' => 'foreign',
            ),
        ),
    );

}
