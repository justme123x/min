<?php
namespace MinPHP\Base;

/**
 * 路由类
 * @method static Router get(string $route, Callable $callback)
 * @method static Router post(string $route, Callable $callback)
 * @method static Router put(string $route, Callable $callback)
 * @method static Router delete(string $route, Callable $callback)
 * @method static Router options(string $route, Callable $callback)
 * @method static Router head(string $route, Callable $callback)
 * @method static Router group(array $options, Callable $callback)
 *
 * 1. __callStatic() 接管请求方法。接受两个参数，$method 和 $params，前者是具体的 function 名称。
 * 2. __callStatic() 做的事情也很简单，分别将目标URL、HTTP方法和回调代码压入 $routes、$methods 和 $callbacks 三个 Router 类的静态成员变量（数组）中。
 * 3. dispatch(); 方法才是真正处理当前 URL 的地方。能直接匹配到的会直接调用回调，不能直接匹配到的将利用正则进行匹配。
 */
class Router
{

    public static $halts = false;
    public static $routes = array();
    public static $methods = array();
    public static $callbacks = array();
    public static $patterns = array(
        ':any' => '[^/]+',
        ':num' => '[0-9]+',
        ':all' => '.*'
    );
    public static $error_callback;
    private static $namespace;
    private static $prefix;

    /**
     * 定义路由
     */
    public static function __callStatic($method, $params)
    {
        if ($method === 'group') {

            // 处理路由组
            if (is_object($params[1])) {
                $group_func = &$params[1];
                isset($params[0]['namespace']) && self::$namespace = $params[0]['namespace'];
                isset($params[0]['prefix']) && self::$prefix = $params[0]['prefix'];
                $group_func();
                return;
            }

            self::$namespace = '';
            self::$namespace = '';

        }

        $uri = dirname($_SERVER['PHP_SELF']) . '/' . self::$prefix.$params[0];
        $callback = $params[1];
        array_push(self::$routes, $uri);
        array_push(self::$methods, strtoupper($method));
        array_push(self::$callbacks, self::$namespace . '\\' . $callback);

    }

    /**
     * 定义错误页面
     * @param $callback
     */
    public static function error($callback)
    {
        self::$error_callback = $callback;
    }

    public static function haltOnMatch($flag = true)
    {
        self::$halts = $flag;
    }

    /**
     * 执行回调函数饼传递REQUEST对象
     */
    public static function dispatch()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        $searches = array_keys(static::$patterns);
        $replaces = array_values(static::$patterns);
        $found_route = false;
        self::$routes = preg_replace('/\/+/', '/', self::$routes);

        // 完全匹配请求路由 是否在预定义路由表达式中
        if (in_array($uri, self::$routes)) {

            $route_pos = array_keys(self::$routes, $uri);
            foreach ($route_pos as $route) {

                // 使用ANY匹配GET和POST请求
                if (self::$methods[$route] == $method || self::$methods[$route] == 'ANY') {

                    $found_route = true;

                    // 启动请求
                    self::_run($route,[]);
                    break;

                }

            }

        } else {

            //正则匹配
            $pos = 0;

            foreach (self::$routes as $route) {

                // 是否有预定义正则表达式
                if (strpos($route, ':') !== false) {
                    $route = str_replace($searches, $replaces, $route);
                }

                // 开始正常匹配路由
                if (preg_match('#^' . $route . '$#', $uri, $matched)) {

                    // 匹配成功 检查请求方式
                    if (self::$methods[$pos] == $method || self::$methods[$pos] == 'ANY') {
                        $found_route = true;


                        dd(explode('/',$route));
                        array_shift($matched);

                        // 启动请求
                        self::_run($uri,$pos);
                        break;

                    }

                }
                $pos++;

            }
        }

        //没有匹配到路由 执行错误回调函数 返回404错误
        if ($found_route == false) {

            if (!self::$error_callback) {
                self::$error_callback = function () {
                    header($_SERVER['SERVER_PROTOCOL'] . " 404 Not Found");
                    echo '404';
                };
            } else {
                if (is_string(self::$error_callback)) {
                    self::get($_SERVER['REQUEST_URI'], self::$error_callback);
                    self::$error_callback = null;
                    self::dispatch();
                    return;
                }
            }
            call_user_func(self::$error_callback);
        }

    }

    public static function _run($routeKey,$params = [])
    {
        if (!is_object(self::$callbacks[$routeKey])) {

            //实例化请求
            dd(self::$callbacks[$routeKey]);
            $parts = explode('@', self::$callbacks[$routeKey]);
            $last = end($parts);
            $controller = new $parts[0]();

            if (!method_exists($controller, $last)) {

                echo "请求页面不存在";

            } else {

                call_user_func_array(array($controller, $last), $params);

            }

            if (self::$halts) return;

        } else {

            call_user_func(self::$callbacks[$routeKey]);
            if (self::$halts) return;

        }
    }
}