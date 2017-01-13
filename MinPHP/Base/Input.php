<?php
namespace MinPHP\Base;
class Input
{
    /**
     * 获取GET参数
     * @param $field                string  字段名称
     * @param string $defaultValue  string  默认值
     * @param null $filter          string  过滤函数名称
     * @return string
     */
    public static function Get($field, $defaultValue = '', $filter = null)
    {
        if (!isset($_GET[$field])) return $defaultValue;
        if (!isset($filter)) return $_GET[$field];
        return $$filter($_GET[$field]);
    }

    /**
     * 获取POST参数
     * @param $field                string  字段名称
     * @param string $defaultValue  string  默认值
     * @param null $filter          string  过滤函数名称
     * @return string
     */
    public static function POST($field, $defaultValue = '', $filter = null)
    {
        if (!isset($_POST[$field])) return $defaultValue;
        if (!isset($filter)) return $_POST[$field];
        return $$filter($_POST[$field]);
    }

    /**
     * 获取Request参数
     * @param $field                string  字段名称
     * @param string $defaultValue  string  默认值
     * @param null $filter          string  过滤函数名称
     * @return string
     */
    public static function Request($field, $defaultValue = '', $filter = null)
    {
        if (!isset($_REQUEST[$field])) return $defaultValue;
        if (!isset($filter)) return $_REQUEST[$field];
        return $$filter($_REQUEST[$field]);
    }
}