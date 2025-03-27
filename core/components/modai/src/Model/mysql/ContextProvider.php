<?php
namespace modAI\Model\mysql;

use xPDO\xPDO;

class ContextProvider extends \modAI\Model\ContextProvider
{

    public static $metaMap = array (
        'package' => 'modAI\\Model\\',
        'version' => '3.0',
        'table' => 'modai_context_provider',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'class' => '',
            'name' => '',
            'description' => '',
            'properties' => '',
        ),
        'fieldMeta' => 
        array (
            'class' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '200',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'name' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '200',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'description' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '500',
                'phptype' => 'string',
                'null' => true,
                'default' => '',
            ),
            'properties' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'array',
                'null' => true,
                'default' => '',
            ),
        ),
        'composites' => 
        array (
            'Agents' => 
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
