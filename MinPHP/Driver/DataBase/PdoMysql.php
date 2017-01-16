<?php
namespace MinPHP\Driver\DataBase;

use MinPHP\Interfaces\DataBaseInterface;

/**
 * PDO驱动MySQL
 * Class PdoMysql
 * @author pengcy123x@foxmail.com
 * @package MinPHP\Driver\DataBase
 */
class PdoMysql implements DataBaseInterface
{
    // 连接配置信息
    private $config;

    // 主从 写服务器
    private $wLink;

    // 主从 读服务器
    private $rLink;

    public function __construct(&$config)
    {
        $this->config = $config;
    }

    public function __destruct()
    {
        !empty($this->wLink) && $this->wLink = null;
        !empty($this->rLink) && $this->rLink = null;
    }

    /**
     * 获取从库连接
     * 当只有一台服务器时,则返回主服务器链接
     * @return resource
     */
    public function getRLink()
    {

        if ($this->rLink) return $this->rLink;

        $slave_num = count($this->config['slave']);

        if ($slave_num === 0) return $this->rLink = $this->getWLink();

        $key = $slave_num - 1 && $key = rand(0, $slave_num - 1);

        return $this->wLink = $this->connect($this->config['slave'][$key]);

    }

    /**
     * 获取写库连接
     * 当只有一台服务器时,则返回主服务器链接
     * @return resource
     */
    public function getWLink()
    {

        if ($this->wLink) return $this->wLink;

        $master_num = count($this->config['master']);

        $key = $master_num - 1 && $key = rand(0, $master_num - 1);

        return $this->wLink = $this->connect($this->config['master'][$key]);

    }

    /**
     * 获取服务器链接
     * @return resource
     */
    public function connect(&$single)
    {

        try {

            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;', $single['db_host'], $single['db_port'], $single['db_database_name']);
            $attr = [
                \PDO::ATTR_TIMEOUT => 5,
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $single['db_charset']
            ];
            return new \PDO($dsn, $single['db_user'], $single['db_password'], $attr);

        } catch (\PDOException $e) {

            throw new \Exception($this->_getMysqlSafeError($e));

        }

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

}