<?php
namespace MinPHP\Driver\DataBase;

use MinPHP\Interfaces\DataBaseInterface;

class PdoMysql implements DataBaseInterface
{
    //表前缀
    public $tablePrefix = 'web_';
    public $table = 'category';

    //表主键
    public $primaryKey = 'category_id';

    //连接配置信息
    private $config;

    //主从 写服务器
    private $wLink;

    //主从 读服务器
    private $rLink;

    private $sql = '';
    private $field = [];
    private $where = [];
    private $order = [];
    private $limit = [];
    private $data = [];
    private $bind = [];

    public function __construct(&$config)
    {
        $this->config = $config;
        $this->tablePrefix = $config['db_table_prefix'];
    }

    /**
     * 获取最后执行的SQL语句
     * @return string
     */
    public function getLastSql()
    {
        return $this->sql;
    }

    /**
     * 设置查询字段
     * @param array $field
     * @return $this
     */
    public function field(array $field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * 设置排序条件
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
     * @param array $data
     * @return $this
     */
    public function data(array $data)
    {
        $this->data = $data;
        return $this;
    }


    public function insert()
    {
        return 1;
    }

    public function update()
    {
    }

    // 获取多条
    public function delete()
    {
    }

    /**
     * 查询一条SQL 查询所有行
     * @param $sql
     */
    public function query($sql)
    {

        if (empty($sql)) return false;

        try {

            if (!$result = $this->_getRLink()->query($sql)) return false;

            $result->setFetchMode(\PDO::FETCH_ASSOC);
            return $result->fetchAll();

        } catch (\PDOException $e) {

            throw new \Exception($this->_getMysqlSafeError($e));

        }

    }

    /**
     * 查询一条
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

        $stmt = $this->_prepare($this->_getQuerySql());

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        unset($stmt);

        return $result;
    }

    /**
     * 查询返回所有记录
     * @return mixed
     * @throws \Exception
     */
    public function select()
    {
        $stmt = $this->_prepare($this->_getQuerySql());

        $result = $stmt->fetchAll();
        $stmt->closeCursor();

        unset($stmt);

        return $result;
    }

    // 查询
    public function exec($sql)
    {
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
        $fields = empty($this->field) ? '*' : '`' . implode('`,`', $this->field) . '`';
        $table = $this->_getFullTableName();
        $where = $this->_getWhereCondition();
        $order = $this->_getOrder();
        $limit = empty($this->limit) ? '' : 'LIMIT ' . implode(',', $this->limit);
        $this->sql = trim(sprintf('SELECT %s FROM `%s` %s %s %s', $fields, $table, $where, $order, $limit));
        array_push($_ENV['_sqls'], $this->sql);
        return $this->sql;
    }

    /**
     * 获取完整表名
     * @return string
     */
    private function _getFullTableName()
    {
        return $this->tablePrefix . $this->table;
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
    private function _prepare($sql)
    {

        $stmt = $this->_getRLink()->prepare($sql);

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

    private function _getMysqlSafeError(\PDOException $e)
    {
        if (DEBUG) return $e->getMessage();
        if ($e->getCode() == 1049) {
            return '数据库名不存在，请手工创建';
        } elseif ($e->getCode() == 2003) {
            return '连接数据库服务器失败，请检查IP是否正确，或者防火墙设置';
        } elseif ($e->getCode() == 1024) {
            return '连接数据库失败';
        } elseif ($e->getCode() == 1045) {
            return '数据库账户密码错误';
        }
        return '数据库错误';
    }

    /**
     * 获取从库连接
     * 当只有一台服务器时,则返回主服务器链接
     * @return PDO
     */
    private function _getRLink()
    {

        if ($this->rLink) return $this->rLink;

        $slave_num = count($this->config['slave']);

        if ($slave_num === 0) return $this->rLink = $this->_getWLink();

        $key = $slave_num - 1 && $key = rand(0, $slave_num - 1);

        return $this->wLink = $this->_connect($this->config['slave'][$key]);

    }

    /**
     * 获取写库连接
     * 当只有一台服务器时,则返回主服务器链接
     * @return PDO
     */
    private function _getWLink()
    {

        if ($this->wLink) return $this->wLink;

        $master_num = count($this->config['master']);

        $key = $master_num - 1 && $key = rand(0, $master_num - 1);

        return $this->wLink = $this->_connect($this->config['master'][$key]);

    }

    /**
     * 获取服务器链接
     * @return PDO
     */
    private function _connect(&$single)
    {

        try {

            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;', $single['db_host'], $single['db_port'], $single['db_database_name']);
            return new \PDO($dsn, $single['db_user'], $single['db_password'], [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $single['db_charset']]);

        } catch (\PDOException $e) {

            throw new \Exception($this->_getMysqlSafeError($e));

        }

    }

}