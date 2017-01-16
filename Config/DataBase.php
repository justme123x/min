<?php
return [

    // 数据库驱动
    'db_driver' => 'MinPHP\Driver\DataBase\PdoMysql',

    // 表前缀
    'db_table_prefix' => 'web_',

    // SQL日志
    'db_log' => true,

    /**
     * 写数据库配置
     * 如果有多个写服务器时，每一组配置一个数组
     *  'master'=>[
     *       [配置1],
     *       [配置2],
     *       [配置3],
     *      ...
     *   ]
     */
    'master' => [
        [
            // 驱动
            'db_driver' => '',

            // IP
            'db_host' => '127.0.0.1',

            // 端口
            'db_port' => '3306',

            // 账号
            'db_user' => 'root',

            // 密码
            'db_password' => '123456',

            // 库名
            'db_database_name' => 'cehuiren',

            // 数据库编码
            'db_charset' => 'utf8',

        ]

    ],

    /**
     * 从数据库配置
     * 如果有多个从库时，每一组从库配置一个数组
     *  'slave'=>[
     *       [从库配置1],
     *       [从库配置2],
     *       [从库配置3],
     *      ...
     *   ]
     */
    'slave' => []

];