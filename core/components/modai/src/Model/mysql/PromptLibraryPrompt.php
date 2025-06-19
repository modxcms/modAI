<?php
namespace modAI\Model\mysql;

use xPDO\xPDO;

class PromptLibraryPrompt extends \modAI\Model\PromptLibraryPrompt
{

    public static $metaMap = array (
        'package' => 'modAI\\Model\\',
        'version' => '3.0',
        'table' => 'modai_prompt_library_prompts',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'name' => NULL,
            'prompt' => NULL,
            'enabled' => 0,
            'public' => 0,
            'rank' => 0,
            'category_id' => NULL,
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
            'prompt' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'string',
                'null' => false,
            ),
            'enabled' => 
            array (
                'dbtype' => 'tinyint',
                'precision' => '1',
                'phptype' => 'boolean',
                'null' => false,
                'default' => 0,
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
            'category_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'int',
                'null' => false,
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
            'category_id' => 
            array (
                'alias' => 'category_id',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'category_id' => 
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
            'enabled_rank' => 
            array (
                'alias' => 'enabled_rank',
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
                    'rank' => 
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
            'Category' => 
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
