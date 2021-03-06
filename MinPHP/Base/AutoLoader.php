<?php
namespace MinPHP\Base;

/**
 * 自动加载类
 * Class AutoLoader
 * @author pengcy123x@foxmail.com
 * @package MinPHP\Base
 */
class AutoLoader
{
    public static function AutoLoader($className)
    {
        $filePath = SITE_PATH . '/' . str_replace('\\', '/', $className) . '.php';

        if (is_file($filePath)) {

            include $filePath;

        } else {

            $exceptionStr = sprintf("类文件：%s 不存在！", $className);
            throw new \Exception($exceptionStr);

        }

    }
}

spl_autoload_register(['MinPHP\Base\AutoLoader', 'AutoLoader']);