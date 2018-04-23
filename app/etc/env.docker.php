<?php
return array (
    'backend' =>
        array (
            'frontName' => 'admin',
        ),
    'db' =>
        array (
            'connection' =>
                array (
                    'indexer' =>
                        array (
                            'host' => 'db',
                            'dbname' => 'sport_dog_food',
                            'username' => 'sport_dog_food',
                            'password' => 'sport_dog_food',
                            'model' => 'mysql4',
                            'engine' => 'innodb',
                            'initStatements' => 'SET NAMES utf8;',
                            'active' => '1',
                            'persistent' => NULL,
                        ),
                    'default' =>
                        array (
                            'host' => 'db',
                            'dbname' => 'sport_dog_food',
                            'username' => 'sport_dog_food',
                            'password' => 'sport_dog_food',
                            'model' => 'mysql4',
                            'engine' => 'innodb',
                            'initStatements' => 'SET NAMES utf8;',
                            'active' => '1',
                        ),
                ),
            'table_prefix' => '',
        ),
    'crypt' =>
        array (
            'key' => '0ef1ce939b4b4aaa563996a6bbcf9cc1',
        ),
    'resource' =>
        array (
            'default_setup' =>
                array (
                    'connection' => 'default',
                ),
        ),
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'developer',
    'session' =>
        array (
            'save' => 'redis',
            'redis' =>
                array (
                    'host' => 'redis',
                    'port' => '6379',
                    'database' => '2',
                ),
        ),
    'cache_types' =>
        array (
            'config' => 1,
            'layout' => 1,
            'block_html' => 1,
            'collections' => 1,
            'reflection' => 1,
            'db_ddl' => 1,
            'eav' => 1,
            'customer_notification' => 1,
            'full_page' => 0,
            'config_integration' => 1,
            'config_integration_api' => 1,
            'target_rule' => 1,
            'translate' => 1,
            'config_webservice' => 1,
        ),
    'install' =>
        array (
            'date' => 'Mon, 19 Feb 2018 18:01:21 +0000',
        ),
    'system' =>
        array (
            'default' =>
                array (
                    'catalog' =>
                        array (
                            'search' =>
                                array (
                                    'engine' => 'elasticsearch',
                                    'elasticsearch_server_hostname' => 'elasticsearch',
                                ),
                        ),
                    'system' =>
                        array (
                            'full_page_cache' =>
                                array (
                                    'caching_application' => '1',
                                ),
                        ),
                    'newrelicreporting' =>
                        array (
                            'general' =>
                                array (
                                    'enable' => '0',
                                ),
                        ),
                    'google' =>
                        array (
                            'analytics' =>
                                array (
                                    'active' => '0',
                                ),
                        ),
                    'web' =>
                        array (
                            'unsecure' =>
                                array (
                                    'base_url' => 'http://sportdogfood.test/',
                                ),
                            'secure' =>
                                array (
                                    'base_url' => 'http://sportdogfood.test/',
                                ),
                        ),
                ),
        ),
    'cache' =>
        array (
            'frontend' =>
                array (
                    'page_cache' =>
                        array (
                            'backend' => 'Cm_Cache_Backend_Redis',
                            'backend_options' =>
                                array (
                                    'server' => 'redis',
                                    'database' => '1',
                                    'port' => '6379',
                                    'compress_data' => '0',
                                ),
                        ),
                    'default' =>
                        array (
                            'backend' => 'Cm_Cache_Backend_Redis',
                            'backend_options' =>
                                array (
                                    'server' => 'redis',
                                    'database' => '0',
                                    'port' => '6379',
                                ),
                        ),
                ),
        ),
);
