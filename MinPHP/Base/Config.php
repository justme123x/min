<?php
namespace MinPHP\Base;
class Config
{
    private static $config = [];

    /**
     * 初始化配置文件
     * 一次加载所有配置文件到内存中
     */
    public static function Init()
    {
        if (!empty(self::$config)) return;
        array_push(self::$config, include CONFIG_PATH . 'Session.php');
    }

    /**
     * 获取配置项的值
     * @param $key
     * @param string $defaultValue
     * @return string
     */
    public static function get($key, $defaultValue = '')
    {
        if (isset(self::$config[$key])) return self::$config[$key];
        return $defaultValue;
    }
}