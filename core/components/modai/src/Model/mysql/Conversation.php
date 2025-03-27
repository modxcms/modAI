<?php
namespace modAI\Model\mysql;

use xPDO\xPDO;

class Conversation extends \modAI\Model\Conversation
{

    public static $metaMap = array (
        'package' => 'modAI\\Model\\',
        'version' => '3.0',
        'table' => 'modai_conversation',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'title' => 'New message',
            'started_by' => 0,
            'started_on' => 0,
            'last_message_on' => 0,
            'visible_history' => 0,
            'prompt_token_count' => 0,
            'response_token_count' => 0,
        ),
        'fieldMeta' => 
        array (
            'title' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '190',
                'phptype' => 'string',
                'null' => false,
                'default' => 'New message',
            ),
            'started_by' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'int',
                'null' => false,
                'default' => 0,
                'attributes' => 'unsigned',
            ),
            'started_on' => 
            array (
                'dbtype' => 'int',
                'precision' => '20',
                'phptype' => 'int',
                'null' => false,
                'default' => 0,
                'attributes' => 'unsigned',
            ),
            'last_message_on' => 
            array (
                'dbtype' => 'int',
                'precision' => '20',
                'phptype' => 'int',
                'null' => false,
                'default' => 0,
                'attributes' => 'unsigned',
            ),
            'visible_history' => 
            array (
                'dbtype' => 'tinyint',
                'precision' => '1',
                'phptype' => 'boolean',
                'null' => false,
                'default' => 0,
            ),
            'prompt_token_count' => 
            array (
                'dbtype' => 'int',
                'precision' => '20',
                'phptype' => 'int',
                'null' => false,
                'default' => 0,
                'attributes' => 'unsigned',
            ),
            'response_token_count' => 
            array (
                'dbtype' => 'int',
                'precision' => '20',
                'phptype' => 'int',
                'null' => false,
                'default' => 0,
                'attributes' => 'unsigned',
            ),
        ),
        'indexes' => 
        array (
            'started_by' => 
            array (
                'alias' => 'started_by',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'started_by' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'started_on' => 
            array (
                'alias' => 'started_on',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'started_on' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'last_message_on' => 
            array (
                'alias' => 'last_message_on',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'last_message_on' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'prompt_token_count' => 
            array (
                'alias' => 'prompt_token_count',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'prompt_token_count' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'response_token_count' => 
            array (
                'alias' => 'response_token_count',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'response_token_count' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'visible_history' => 
            array (
                'alias' => 'visible_history',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'visible_history' => 
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
            'Messages' => 
            array (
                'cardinality' => 'many',
                'class' => 'modAI\\Model\\Message',
                'foreign' => 'conversation',
                'local' => 'id',
                'owner' => 'local',
            ),
        ),
        'aggregates' => 
        array (
            'StartedBy' => 
            array (
                'cardinality' => 'one',
                'class' => 'modUser',
                'foreign' => 'id',
                'local' => 'started_by',
                'owner' => 'foreign',
            ),
        ),
    );

}
