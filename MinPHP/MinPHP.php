<?php
//框架版本
define('MIN_VERSION', 'BASE');

//调试模式
defined('DEBUG') || define('DEBUG', 2);

//记录运行开始时间
$_ENV['_start_time'] = microtime(1);

//记录内存初始使用
define('MEMORY_INFO_ON', function_exists('memory_get_usage'));
MEMORY_INFO_ON AND $_ENV['_start_memory'] = memory_get_usage();

require MIN_PATH . 'Base/Function.php';
require MIN_PATH . 'Base/AutoLoader.php';

\MinPHP\Base\Core::Start();
