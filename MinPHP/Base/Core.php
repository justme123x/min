<?php
namespace MinPHP\Base;


use MinPHP\Driver\DataBase\PdoMysql;

class Core
{
    public static function Start()
    {
        Debug::Init();
        self::InitEnv();
        self::InitSession();
        self::InitHeader();
        self::InitController();

        $db = new PdoMysql($_ENV['_conf']['database']);
        $where = array(
//            'category_id' => 27,
//            'groupid' => 123,
            //'category_id'=>array(25,26,27),
            //'category_name' => array('LIKE' => '%西安%'),
            'category_id'=>array('>' => 1, '<' => 200)
        );
        $field = ['category_name','category_alias','category_keywords'];
        $order = ['category_parent_id'=>'asc','category_id'=>'desc'];
        $limit  = [1,3];


        $re = $db->field($field)->where($where)->orderBy($order)->limit($limit)->find(27);
        $data= [
            'config_id'=>100,
            'config_field'=>substr(md5(time()),0,-12),
            'config_name'=>123456,
            'config_value'=>654321,
            'config_comment'=>time(),
        ];
        $re =$db->data($data)->insert();

        dd($db->version());
        dd($re,$db->getLastSql());
        //dd($db->query('SELECT * FROM `cehui_adminsss`'));
    }

    /**
     * 初始化头信息
     */
    private static function InitHeader()
    {

        header("Expires: 0");
        header("Cache-Control: private, post-check=0, pre-check=0, max-age=0");
        header("Pragma: no-cache");
        header('Content-Type: text/html; charset=UTF-8');

    }

    /**
     * 初始化环境变量
     */
    private static function InitEnv()
    {
        $_ENV['_sqls'] = array();    // debug 时使用
        $_ENV['_time'] = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
        $_ENV['_ip'] = ip();
        $_ENV['_sqlnum'] = 0;
        $_ENV['_conf']['session'] = include CONFIG_PATH . 'Session.php';
        $_ENV['_conf']['database'] = include CONFIG_PATH . 'DataBase.php';
    }

    /**
     * 初始化Session相关信息
     */
    private static function InitSession()
    {
        if (!function_exists('ini_set')) return;
        ini_set('session.auto_start', 0);

        $conf = &$_ENV['_conf']['session'];

        // session 会话驱动
        if ($conf['session_driver'] != 'files') {
            $sessionHandler = new $conf['session_driver']();
            $sessionHandler instanceof \SessionHandlerInterface && session_set_save_handler($sessionHandler, true);
        }


        ini_set('session.cookie_lifetime', $conf['session_lifetime']);         //cookie有效时间
        ini_set('session.gc_maxlifetime', $conf['session_lifetime']);         //session有效时间
        ini_set('session.cookie_path', $conf['session_path']);                //cookie作用域
        ini_set('session.cookie_httponly', $conf['session_http_only']);       //是否仅http模式
        ini_set('session.save_path', $conf['session_save_path']);             //session存储路径
        session_start();
    }

    /**
     * 初始化请求
     */
    private static function InitController()
    {
        $app = Input::Get('p', 'Home');
        $controller = Input::Get('c', 'Index');
        $action = Input::Get('a', 'Index');
        $controllerNameSpace = '\\App\\Http\\' . $app . '\\' . $controller . 'Controller';
        $controllerObject = new $controllerNameSpace();

        // 请求方法是否存在
        if (!method_exists($controllerObject, $action)) {
            $errorMessage = sprintf('控制器：%s 请求%s 方法不存在.', $controllerNameSpace, $action);
            throw new \Exception($errorMessage);
        }

        //定义模版路径
        $viewPathStr = sprintf('%s/Views/%s/%s/', SITE_PATH, $app, $controller);
        define('VIEW_PATH', $viewPathStr);

        // 开始请求
        $controllerObject->$action();

        // 请求结束
    }
}