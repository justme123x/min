<?php
/**
 * 辅助函数库
 */

// 打印调试函数
if (!function_exists('dd')) {
    function dd()
    {
        echo '<pre style="background-color: black;color: green;padding: 10px">';
        echo "\r\n\r\n";
        $args = func_get_args();
        foreach ($args as $k => $v) {
            var_dump($v);
            echo "\r\n\r\n";
        }
        echo '</pre>';
        exit;
    }
}

if (!function_exists('arr_html')) {
    // 递归转换为HTML实体代码
    function arr_html(&$var)
    {
        if (is_array($var)) {
            foreach ($var as $k => &$v) arr_html($v);
        } else {
            $var = htmlspecialchars($var);
        }
    }
}

// 统计程序运行时间
if (!function_exists('run_time')) {
    function run_time()
    {
        return number_format(microtime(1) - $_ENV['_start_time'], 4);
    }
}

if (!function_exists('run_mem')) {
    // 统计程序内存开销
    function run_mem()
    {
        return MEMORY_INFO_ON ? get_byte(memory_get_usage() - $_ENV['_start_memory']) : 'unknown';
    }
}


if (!function_exists('get_byte')) {
    /**
     * 获取数据大小单位
     * @param int $byte 字节
     * @return string
     */
    function get_byte($byte)
    {
        if ($byte < 1024) {
            return $byte . ' Byte';
        } elseif ($byte < 1048576) {
            return round($byte / 1024, 2) . ' KB';
        } elseif ($byte < 1073741824) {
            return round($byte / 1048576, 2) . ' MB';
        } elseif ($byte < 1099511627776) {
            return round($byte / 1073741824, 2) . ' GB';
        } else {
            return round($byte / 1099511627776, 2) . ' TB';
        }
    }
}

if (!function_exists('ip')) {
    // 安全获取IP
    function ip()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            preg_match('#[\d\.]{7,15}#', $_SERVER['HTTP_X_FORWARDED_FOR'], $mat);
            $ip = $mat[0];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return long2ip(ip2long($ip));
    }
}
