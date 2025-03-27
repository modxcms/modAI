<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

global $table_prefix, $database_dsn, $database_user, $database_password, $driver_options;

require_once __DIR__ . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CORE_PATH . 'vendor/autoload.php';
require_once __DIR__ . '/core/components/modai/vendor/autoload.php';

use xPDO\xPDO;
return [
    'mysql_array_options' => [
        xPDO::OPT_CACHE_PATH => MODX_CORE_PATH . 'cache/xpdo/',
        xPDO::OPT_TABLE_PREFIX => $table_prefix,
        xPDO::OPT_HYDRATE_FIELDS => true,
        xPDO::OPT_HYDRATE_RELATED_OBJECTS => true,
        xPDO::OPT_HYDRATE_ADHOC_FIELDS => false,
        xPDO::OPT_CONNECTIONS => [
            [
                'dsn' => $database_dsn,
                'username' => $database_user,
                'password' => $database_password,
                'options' => [
                    xPDO::OPT_CONN_MUTABLE => true,
                ],
                'driverOptions' => $driver_options,
            ],
        ],
    ]
];
