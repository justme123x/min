<?php
namespace MinPHP\Interfaces;
/**
 * 数据库连接读写分类接口
 * Interface DataBaseInterface
 * @author pengcy123x@foxmail.com
 * @package MinPHP\Interfaces
 */
interface DataBaseInterface
{
    /**
     * 获取从库连接
     * 当只有一台服务器时,则返回主服务器链接
     * @return resource
     */
    public function getRLink();

    /**
     * 获取写库连接
     * 当只有一台服务器时,则返回主服务器链接
     * @return resource
     */
    public function getWLink();

    /**
     * 获取服务器链接
     * @return resource
     */
    public function connect(&$single);
}