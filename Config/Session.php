<?php
return [

    /**
     * Session 驱动
     * 使用其他驱动如下方式
     * 'session_driver' => \MinPHP\Driver\Session\MysqlSession::class,
     */
    'session_driver' => 'files',

    // 有效时间
    'session_lifetime' => 0,

    // 会话临时文件保存路径
    'session_save_path' => RUN_PATH . 'Sessions',

    // Session作用域
    'session_path' => '/',

    // 是否仅http
    'session_http_only' => true
];