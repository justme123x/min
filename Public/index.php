<?php
/**
 * 入口文件
 *
 */

version_compare(PHP_VERSION, '5.4.0', '<') AND die('require PHP > 5.4.0');
// 1:开发模式   2：线上调试：日志记录     0关闭
define('DEBUG', 1);
define('SITE_PATH', realpath(dirname($_SERVER['SCRIPT_FILENAME']) . '/../'));
define('APP_PATH', SITE_PATH . '/App/');
define('LOG_PATH', SITE_PATH . '/Log/');
define('CONFIG_PATH', SITE_PATH . '/Config/');
define('RUN_PATH', SITE_PATH . '/Runtime/');
define('MIN_PATH', SITE_PATH . '/MinPHP/');

require MIN_PATH . 'MinPHP.php';
