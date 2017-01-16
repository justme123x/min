<?php
namespace MinPHP\Interfaces;

/**
 * 数据模型基类接口
 * Interface ModelInterface
 * @author pengcy123x@foxmail.com
 * @package MinPHP\Interfaces
 */
interface ModelInterface
{
    //查询字段
    public function field($field);

    // 条件
    public function where(array $where);

    // 排序
    public function orderBy(array $order);

    // 限定结果集
    public function limit(array $limit);

    // 获取一条
    public function find($primaryKey = null);

    // 获取多条
    public function select();

    // 统计
    public function count();

    // 更新 插入 数据
    public function data(array $data);

    // 插入
    public function insert();

    // 更新
    public function update();

    // 删除
    public function delete();

    // 获取mysql版本
    public function version();

    // 查询
    public function query($sql);

    // 执行
    public function exec($sql);
}
