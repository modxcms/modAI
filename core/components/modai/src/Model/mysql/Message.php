<?php
namespace modAI\Model\mysql;

use xPDO\xPDO;

class Message extends \modAI\Model\Message
{

    public static $metaMap = array (
        'package' => 'modAI\\Model\\',
        'version' => '3.0',
        'table' => 'modai_messages',
        'extends' => 'xPDO\\Om\\xPDOObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'internalId' => NULL,
            'chat' => NULL,
            'type' => NULL,
            'id' => NULL,
            'role' => NULL,
            'content' => NULL,
            'content_type' => NULL,
            'tool_calls' => NULL,
            'contexts' => NULL,
            'attachments' => NULL,
            'metadata' => NULL,
            'ctx' => NULL,
            'hidden' => 0,
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'created_on' => 'CURRENT_TIMESTAMP',
            'created_by' => 0,
        ),
        'fieldMeta' => 
        array (
            'internalId' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'null' => false,
                'index' => 'pk',
                'generated' => 'native',
                'extra' => 'auto_increment',
            ),
            'chat' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'int',
                'null' => false,
                'attributes' => 'unsigned',
            ),
            'type' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '190',
                'phptype' => 'string',
                'null' => false,
            ),
            'id' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '190',
                'phptype' => 'string',
                'null' => false,
            ),
            'role' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '190',
                'phptype' => 'string',
                'null' => false,
            ),
            'content' => 
            array (
                'dbtype' => 'longtext',
                'phptype' => 'string',
                'null' => true,
            ),
            'content_type' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '190',
                'phptype' => 'string',
                'null' => true,
            ),
            'tool_calls' => 
            array (
                'dbtype' => 'longtext',
                'phptype' => 'json',
                'null' => true,
            ),
            'contexts' => 
            array (
                'dbtype' => 'longtext',
                'phptype' => 'json',
                'null' => true,
            ),
            'attachments' => 
            array (
                'dbtype' => 'longtext',
                'phptype' => 'json',
                'null' => true,
            ),
            'metadata' => 
            array (
                'dbtype' => 'longtext',
                'phptype' => 'json',
                'null' => true,
            ),
            'ctx' => 
            array (
                'dbtype' => 'longtext',
                'phptype' => 'json',
                'null' => true,
            ),
            'hidden' => 
            array (
                'dbtype' => 'tinyint',
                'precision' => '1',
                'attributes' => 'unsigned',
                'phptype' => 'boolean',
                'null' => false,
                'default' => 0,
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
            'created_on' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ),
            'created_by' => 
            array (
                'dbtype' => 'int',
                'precision' => '11',
                'phptype' => 'int',
                'null' => false,
                'default' => 0,
                'attributes' => 'unsigned',
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
                    'internalId' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'chat_id' => 
            array (
                'alias' => 'chat_id',
                'primary' => false,
                'unique' => true,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'chat' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'chat' => 
            array (
                'alias' => 'chat',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'chat' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'chat_on' => 
            array (
                'alias' => 'chat_on',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'chat' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'created_on' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
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
        ),
        'aggregates' => 
        array (
            'Chat' => 
            array (
                'cardinality' => 'one',
                'class' => 'modAI\\Model\\Chat',
                'foreign' => 'id',
                'local' => 'chat',
                'owner' => 'foreign',
            ),
            'User' => 
            array (
                'cardinality' => 'one',
                'class' => 'MODX\\Revolution\\modUser',
                'foreign' => 'id',
                'local' => 'created_by',
                'owner' => 'foreign',
            ),
        ),
    );

}
