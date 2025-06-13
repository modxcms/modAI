<?php
namespace modAI\Model\mysql;

use xPDO\xPDO;

class Chat extends \modAI\Model\Chat
{

    public static $metaMap = array (
        'package' => 'modAI\\Model\\',
        'version' => '3.0',
        'table' => 'modai_chats',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'title' => 'New message',
            'type' => NULL,
            'created_by' => 0,
            'created_on' => 'CURRENT_TIMESTAMP',
            'last_message_on' => NULL,
            'last_message_id' => NULL,
            'pinned' => 0,
            'public' => 1,
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
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
            'type' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '190',
                'phptype' => 'string',
                'null' => false,
            ),
            'created_by' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'int',
                'null' => false,
                'default' => 0,
                'attributes' => 'unsigned',
            ),
            'created_on' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ),
            'last_message_on' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => true,
            ),
            'last_message_id' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '190',
                'phptype' => 'string',
                'null' => true,
            ),
            'pinned' => 
            array (
                'dbtype' => 'tinyint',
                'precision' => '1',
                'attributes' => 'unsigned',
                'phptype' => 'boolean',
                'null' => false,
                'default' => 0,
            ),
            'public' => 
            array (
                'dbtype' => 'tinyint',
                'precision' => '1',
                'attributes' => 'unsigned',
                'phptype' => 'boolean',
                'null' => false,
                'default' => 1,
            ),
            'prompt_tokens' => 
            array (
                'dbtype' => 'int',
                'precision' => '20',
                'phptype' => 'int',
                'null' => false,
                'default' => 0,
                'attributes' => 'unsigned',
            ),
            'completion_tokens' => 
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
            'created_by' => 
            array (
                'alias' => 'created_by',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'created_by' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'created_on' => 
            array (
                'alias' => 'created_on',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'created_on' => 
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
            'recent_chats' => 
            array (
                'alias' => 'recent_chats',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'created_by' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'public' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'pinned' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'last_message_on' => 
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
                'foreign' => 'chat',
                'local' => 'id',
                'owner' => 'local',
            ),
        ),
        'aggregates' => 
        array (
            'StartedBy' => 
            array (
                'cardinality' => 'one',
                'class' => 'MODX\\Revolution\\modUser',
                'foreign' => 'id',
                'local' => 'started_by',
                'owner' => 'foreign',
            ),
        ),
    );

}
