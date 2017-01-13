<?php
namespace MinPHP\Base;
class Log
{
    public static function Write($message, $file = 'php_error.log')
    {
        $time = date('Y-m-d H:i:s', $_ENV['_time']);
//        $ip = $_ENV['_ip'];
        $ip = '127.0.0.1';
        $url = self::ToStr($_SERVER['REQUEST_URI']);
        $message = self::ToStr($message);
        self::WriteLog("$time	$ip	$url	$message	\r\n", $file);
        return TRUE;
    }

    /**
     * 清理空白字符
     * @param string $s 字符串
     * @return string
     */
    public static function ToStr($message)
    {
        return str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $message);
    }

    /**
     * 文件末尾写入日志
     * @param string $s 写入字符串
     * @param string $file 保存文件名
     * @return boolean
     */
    public static function WriteLog($message, $file)
    {
        $logfile = LOG_PATH . $file;
        $path = dirname($logfile);

        if (!is_dir($path)) mkdir(dirname($path), 0666, true);
        if (!is_file($logfile)) file_put_contents($logfile, '');

        try {
            $fp = fopen($logfile, 'ab+');
            if (!$fp) {
                throw new \Exception("写入日志失败，可能文件 $file 不可写或磁盘已满。");
            }
            fwrite($fp, $message);
            fclose($fp);
        } catch (\Exception $e) {
        }
        return TRUE;
    }
}