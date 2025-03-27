<?php
namespace modAI\Model\mysql;

use xPDO\xPDO;

class Message extends \modAI\Model\Message
{

    public static $metaMap = array (
        'package' => 'modAI\\Model\\',
        'version' => '3.0',
        'table' => 'modai_message',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'conversation' => 0,
            'llm_id' => '',
            'tool_call_id' => '',
            'user_role' => 'system',
            'user' => 0,
            'content' => '',
            'tool_calls' => '',
            'created_on' => 0,
            'prompt_token_count' => 0,
            'response_token_count' => 0,
        ),
        'fieldMeta' => 
        array (
            'conversation' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'int',
                'null' => false,
                'default' => 0,
                'attributes' => 'unsigned',
            ),
            'llm_id' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '190',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'tool_call_id' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '190',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'user_role' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '190',
                'phptype' => 'string',
                'null' => false,
                'default' => 'system',
            ),
            'user' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'int',
                'null' => false,
                'default' => 0,
                'attributes' => 'unsigned',
            ),
            'content' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'tool_calls' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'array',
                'null' => true,
                'default' => '',
            ),
            'created_on' => 
            array (
                'dbtype' => 'int',
                'precision' => '20',
                'phptype' => 'int',
                'null' => false,
                'default' => 0,
                'attributes' => 'unsigned',
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
            'conversation' => 
            array (
                'alias' => 'conversation',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'conversation' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'user' => 
            array (
                'alias' => 'user',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'user' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'delivered_on' => 
            array (
                'alias' => 'delivered_on',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'delivered_on' => 
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
        ),
        'aggregates' => 
        array (
            'Conversation' => 
            array (
                'cardinality' => 'one',
                'class' => 'modAI\\Model\\Conversation',
                'foreign' => 'id',
                'local' => 'conversation',
                'owner' => 'foreign',
            ),
            'User' => 
            array (
                'cardinality' => 'one',
                'class' => 'modUser',
                'foreign' => 'id',
                'local' => 'user',
                'owner' => 'foreign',
            ),
        ),
    );

}
