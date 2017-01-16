<?php
namespace MinPHP\Driver\Model;

use MinPHP\Interfaces\ModelInterface;

/**
 * 数据库模型基类
 * Class Model
 * @author pengcy123x@foxmail.com
 * @package MinPHP\Driver\Model
 */
class Model implements ModelInterface
{
    // 表名
    public $table;

    // 表主键
    public $primaryKey;

    private $db;
    private $sql = '';
    private $field = [];
    private $where = [];
    private $order = [];
    private $limit = [];
    private $data = [];
    private $bind = [];

    public function __construct()
    {
        // 表前缀 允许在MODEl中更改表前缀
        isset($this->tablePrefix) || $this->tablePrefix = $_ENV['_conf']['database']['db_table_prefix'];

        // 完整表名
        if (!isset($this->table)) throw new \Exception('请设置表名 table');
        if (!isset($this->primaryKey)) throw new \Exception('请设置表主键字段 primaryKey');

        $this->table = $this->tablePrefix . $this->table;

        // 获取数据库对象
        $db_driver = &$_ENV['_conf']['database']['db_driver'];
        $this->db = new $db_driver($_ENV['_conf']['database']);

        // 初始化数据对象信息
        $this->db->primaryKey = &$this->primaryKey;
        $this->db->table = &$this->table;
    }

    /**
     * 获取最后执行的SQL语句
     * @return string
     */
    public function getLastSql()
    {
        $len = isset($_ENV['_sqls']) ? count($_ENV['_sqls']) : false;
        return $len === false ? '' : $_ENV['_sqls'][$len - 1];
    }

    /**
     * 设置查询字段
     * 格式1：array('name','sex','age) 查询这三个字段
     * 格式2：'count(*) as num' 查询总行数
     * @param array|string $field
     * @return $this
     */
    public function field($field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * 设置排序条件
     * 格式：array('id'=>'DESC,'age=>'ASC)
     * @param array $order
     * @return $this
     */
    public function orderBy(array $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * 限定查询返回结果集
     * 格式：array(0,10). 查询0-10条记录
     * @param array $limit
     * @return $this
     */
    public function limit(array $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * 设置条件
     * 格式1：array('id'=>123, 'gid'=>123)
     * 格式2：array('id'=>array(1,2,3,4,5))
     * 格式3：array('id'=>array('>' => 100, '<' => 200))
     * 格式4：array('username'=>array('LIKE' => 'jack'))
     * @param array $where
     * @return $this
     */
    public function where(array $where)
    {
        $this->where = $where;
        return $this;
    }

    /**
     * 设置更新|插入数据
     * 格式1：array('name'=>'sun')
     * @param array $data
     * @return $this
     */
    public function data(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 插入一条数据
     * 返回最后插入的主键
     * $data= array('name'=>'jake','age'=>23);
     * $re =$db->data($data)->insert();
     * @return mixed
     * @throws \Exception
     */
    public function insert()
    {
        $mt = $this->_prepare($this->getWLink(), $this->_getInsertSql());
        $mt->closeCursor();
        return $this->db->getWLink()->lastInsertId();
    }

    /**
     * 更新数据 返回受影响的行数
     * $where = array('config_id'=>array(100,101));
     * $data = array('config_name'=>'测试');
     * $re =$db->data($data)->where($where)->update();
     * @return int
     * @throws \Exception
     */
    public function update()
    {
        $stmt = $this->_prepare($this->getWLink(), $this->_getUpdateSql());
        return $this->_getRowCount($stmt);
    }

    /**
     * 删除数据 返回受影响的行数
     * $where = array('config_id'=>array(13,14));
     * $re = $db->where($where)->delete();
     * @return int
     * @throws \Exception
     */
    public function delete()
    {
        $stmt = $this->_prepare($this->db->getWLink(), $this->_getDeleteSql());
        return $this->_getRowCount($stmt);
    }

    /**
     * 不安全的查询一条SQL 查询所有行
     * 不推荐使用
     * @param $sql
     */
    public function query($sql)
    {

        if (empty($sql)) return false;

        try {

            $result = $this->db->getRLink()->query($sql);

            if ($result === false) throw new \Exception($sql . ' 执行错误');

            $result->setFetchMode(\PDO::FETCH_ASSOC);
            return $result->fetchAll();

        } catch (\PDOException $e) {

            throw new \Exception($this->_getMysqlSafeError($e));

        }

    }

    /**
     * 不安全的执行一条SQL语句 返回受影响的行数。
     * 不推荐使用
     * @param $sql
     * @return int
     * @throws \Exception
     */
    public function exec($sql)
    {
        if (empty($sql)) return false;

        try {

            $result = $this->db->getWLink()->exec($sql);

            if ($result === false) throw new \Exception($sql . ' 执行错误');

            return $result;

        } catch (\PDOException $e) {

            throw new \Exception($this->_getMysqlSafeError($e));

        }
    }

    /**
     * 查询一条
     * 格式1：$db->find(27);
     * 格式2：$db->field($field)->where($where)->orderBy($order)->find();
     * @param null $primaryKey
     * @return mixed
     * @throws \Exception
     */
    public function find($primaryKey = null)
    {
        $this->limit([0, 1]);

        if (isset($primaryKey)) {
            $this->where([$this->primaryKey => $primaryKey]);
        }

        $stmt = $this->_prepare($this->db->getRLink(), $this->_getQuerySql());

        $result = $stmt->rowCount() ? $stmt->fetch(\PDO::FETCH_ASSOC) : [];

        $stmt->closeCursor();

        unset($stmt);

        return $result;
    }

    /**
     * 查询返回所有记录
     * 格式1：$db->select();
     * 格式2：$db->field($field)->where($where)->orderBy($order)->limit($limit)->select();
     * @return mixed
     * @throws \Exception
     */
    public function select()
    {
        $stmt = $this->_prepare($this->db->getRLink(), $this->_getQuerySql());

        if (!$stmt->rowCount()) {
            return [];
        }
        $result = $stmt->fetchAll();
        $stmt->closeCursor();

        unset($stmt);

        return $result;
    }

    /**
     * 获取查询总行数
     * 格式1：$db->count()
     * 格式2：$db->field($field)->where($where)->count();
     * @return int
     * @throws \Exception
     */
    public function count()
    {
        $this->field('COUNT(*) as num');

        $result = $this->find();

        return $result['num'];
    }

    /**
     * 获取mysql版本
     * @return string
     * @throws \Exception
     */
    public function version()
    {
        $v = $this->query('SELECT version() as v');
        $v && $v = $v[0]['v'];
        return $v;
    }

    /**
     * 获取查询SQL语句
     * @return string
     */
    private function _getQuerySql()
    {
        $fields = empty($this->field) ? '*' : (is_array($this->field) ? '`' . implode('`,`', $this->field) . '`' : $this->field);
        $where = $this->_getWhereCondition();
        $order = $this->_getOrder();
        $limit = empty($this->limit) ? '' : 'LIMIT ' . implode(',', $this->limit);
        $this->sql = trim(sprintf('SELECT %s FROM `%s` %s %s %s', $fields, $this->table, $where, $order, $limit));
        $this->_pushSql();
        return $this->sql;
    }

    /**
     * 获取插入预插入SQL语句
     * @return string
     */
    private function _getInsertSql()
    {
        $fields = '';
        $values = '';
        foreach ($this->data as $k => $v) {
            $fields .= "`{$k}`,";
            $this->_bind($v);
            $values .= '?,';
        }
        $fields = substr($fields, 0, -1);
        $values = substr($values, 0, -1);

        $this->sql = trim(sprintf('INSERT INTO `%s` (%s)VALUES (%s)', $this->table, $fields, $values));
        $this->_pushSql();
        return $this->sql;
    }

    /**
     * 获取预更新SQL语句
     * @return string
     */
    private function _getUpdateSql()
    {
        $values = '';
        foreach ($this->data as $k => $v) {
            $values .= "`{$k}`=?,";
            $this->_bind($v);
        }
        $values = substr($values, 0, -1);
        $this->sql = trim(sprintf('UPDATE `%s` SET %s %s', $this->table, $values, $this->_getWhereCondition()));
        $this->_pushSql();
        return $this->sql;
    }

    /**
     * 获取预删除SQL语句
     * @return string
     */
    private function _getDeleteSql()
    {
        $this->sql = trim(sprintf('DELETE FROM `%s` %s', $this->table, $this->_getWhereCondition()));
        $this->_pushSql();
        return $this->sql;
    }

    /**
     * 生成WHERE条件
     * @return string
     */
    private function _getWhereCondition()
    {
        $s = '';
        if (!empty($this->where)) {
            $s .= ' WHERE ';

            foreach ($this->where as $k => $v) {

                if (!is_array($v)) {

                    $s .= '`' . $k . '` = ? AND ';
                    $this->_bind($v);

                } elseif (isset($v[0])) {

                    // OR 效率比 IN 高
                    $s .= '(';
                    foreach ($v as $v1) {

                        $s .= '`' . $k . '` = ? OR ';
                        $this->_bind($v1);

                    }

                    $s = substr($s, 0, -4);
                    $s .= ') AND ';

                } else {

                    foreach ($v as $k1 => $v1) {

                        $s .= " `{$k}` $k1 ? AND";
                        $this->_bind($v1);

                    }

                }

            }

        }
        return $s = substr($s, 0, -4);
    }

    /**
     * 生成排序条件
     * @return string
     */
    private function _getOrder()
    {
        $order = '';
        if (empty($this->order)) return $order;

        $order .= 'ORDER BY ';

        foreach ($this->order as $k => $v) $order .= $k . ' ' . strtoupper($v) . ',';

        return substr($order, 0, -1);
    }

    /**
     * 压入绑定数组末尾
     * @param $value
     * @return int
     */
    private function _bind($value)
    {
        return array_push($this->bind, $this->_formatBindValue($value));
    }

    /**
     * 格式化绑定值
     * @param $value
     * @return float|int|string
     */
    private function _formatBindValue($value)
    {
        return (is_int($value) || is_float($value)) ? $value : addslashes($value);
    }

    /**
     * PDO预查询一条SQL语句
     * @param $sql
     * @return \PDOStatement
     * @throws \Exception
     */
    private function _prepare($link, $sql)
    {

        $stmt = $link->prepare($sql);

        $stmt->execute($this->bind);
        $this->_reset();

        if ($stmt->errorCode() !== '00000') {
            $error = $stmt->errorInfo();
            $stmt->closeCursor();
            throw new \Exception($error[2]);
        }

        return $stmt;

    }

    /**
     * 获取上一条SQL受影响的函数
     * @param $stmt
     * @return mixed
     */
    private function _getRowCount($stmt)
    {
        $num = $stmt->rowCount();
        $stmt->closeCursor();
        return $num;
    }

    /**
     * 记录执行SQL语句
     * 存储在$_ENV['_sqls']
     */
    private function _pushSql()
    {
        if (empty($this->sql) || !DEBUG) return;

        if (empty($this->bind)) {
            array_push($_ENV['_sqls'], $this->sql);
        } else {
            $sql = implode(',', $this->bind);
            $sql = $this->sql . "\t绑定参数:" . $sql;
            array_push($_ENV['_sqls'], $sql);
        }
    }

    /**
     * 重置对象数据
     * 防止多次查询混淆
     */
    private function _reset()
    {
        $this->field = [];
        $this->where = [];
        $this->order = [];
        $this->limit = [];
        $this->bind = [];
    }


}