<?php
namespace MinPHP\Base;
class Debug
{
    /**
     * 初始化框架DEBUG
     */
    public static function Init()
    {
        if (DEBUG) {

            //调试状态开启错误显示
            error_reporting(E_ALL);
            //程序关闭时执行
            register_shutdown_function(['MinPHP\Base\Debug', 'ShutdownHandler']);

        } else {

            //非调试状态关闭所有错误
            error_reporting(0);

        }

        function_exists('ini_set') && ini_set('display_errors', DEBUG ? '1' : '0');
        set_error_handler(['MinPHP\Base\Debug', 'ErrorHandler']);
        set_exception_handler(['MinPHP\Base\Debug', 'ExceptionHandler']);

    }

    /**
     * 系统错误处理方法
     * @param $errorNo      int     错误的级别
     * @param $errorStr     string  错误信息
     * @param $errorFile    string  错误文件名
     * @param $errorLine    int     错误发生的行号
     * @param $errorText    array   错误上下文
     */
    public static function ErrorHandler($errorNo, $errorStr, $errorFile, $errorLine, $errorText)
    {
        $errorType = array(
            E_ERROR => '运行错误',
            E_WARNING => '运行警告',
            E_PARSE => '语法错误',
            E_NOTICE => '运行通知',
            E_CORE_ERROR => '初始错误',
            E_CORE_WARNING => '初始警告',
            E_COMPILE_ERROR => '编译错误',
            E_COMPILE_WARNING => '编译警告',
            E_USER_ERROR => '用户定义的错误',
            E_USER_WARNING => '用户定义的警告',
            E_USER_NOTICE => '用户定义的通知',
            E_STRICT => '代码标准建议',
            E_RECOVERABLE_ERROR => '致命错误',
            E_DEPRECATED => '代码警告',
            E_USER_DEPRECATED => '用户定义的代码警告',
        );

        $errorTipLevel = isset($errorType[$errorNo]) ? isset($errorType[$errorNo]) : '未知错误';
        $outputErrorStr = "[$errorTipLevel]：$errorStr";

        if (DEBUG) {

            //开发 调试模式
            throw new \Exception($outputErrorStr);

        } else {

            //线上模式 记录日志
            if (in_array($errorNo, array(E_NOTICE, E_USER_NOTICE, E_DEPRECATED))) {
                $logErrorStr = sprintf('文件：%s 第%d行 错误信息：%s', $errorFile, $errorLine, $outputErrorStr);
                Log::Write($logErrorStr);
            } else {
                throw new \Exception($outputErrorStr);
            }

        }
    }

    /**
     * 异常信息处理
     * @param \Exception $e
     */
    public static function ExceptionHandler($e)
    {
        DEBUG && $_ENV['_exception'] = 1;    // 只输出一次

        // 1 定位异常
        $trace = $e->getTrace();

        if (!empty($trace) && $trace[0]['function'] == 'ErrorHandler' && $trace[0]['class'] == 'MinPHP\Base\Debug') {

            // 自定义错误处理函数 触发
            $file = $trace[0]['args'][2];
            $line = $trace[0]['args'][3];

        } else {

            // 系统触发
            $file = $e->getFile();
            $line = $e->getLine();

        }
        // 2 获取异常信息
        $message = $e->getMessage();


        // 3 写日志
        $logStr = "$message File: $file [$line]";
        Log::Write($logStr);

        // 4 输出异常信息
        if (DEBUG) {
            self::ExceptionMessage($message, $file, $line, $e->getTraceAsString());
        } else {

            //线上模式
            $len = strlen($_SERVER['DOCUMENT_ROOT']) + 1;
            $file = substr($file, $len);
            self::SysErrorMessage($message, $file, $line);
        }
    }

    /**
     * 开发 调试 模式下挂载
     * 脚本执行完毕，关闭时执行
     */
    public static function ShutdownHandler()
    {
        $e = error_get_last();
        //无错误信息 或 开发调试下的异常处理只处理一次
        if (!$e || $_ENV['_exception']) return;

        $message = $e['message'];
        $file = $e['file'];
        $line = $e['line'];

        if (DEBUG && DEBUG == 2) {

            //线上调试模式 隐藏绝对路径
            $len = strlen($_SERVER['DOCUMENT_ROOT']) + 1;
            $file = substr($file, $len);

        }

        self::SysErrorMessage($message, $file, $line);
    }

    /**
     * 获取异常文件 代码
     * @param $file     string  文件名
     * @param $line     int     行号
     * @return string
     */
    public static function GetExceptionFileCode($file, $line)
    {
        $fileArr = file($file);
        $fileArrCode = array_slice($fileArr, max(0, $line - 5), 10, true);
        $s = '<table cellspacing="0" width="100%">';
        foreach ($fileArrCode as $i => &$v) {
            $i++;
            $v = htmlspecialchars($v);
            $v = str_replace(' ', '&nbsp;', $v);
            $v = str_replace('	', '&nbsp;&nbsp;&nbsp;&nbsp;', $v);
            $s .= '<tr' . ($i == $line ? ' style="background:#faa;"' : '') . '><td width="40">#' . $i . "</td><td>$v</td>";
        }
        $s .= '</table>';
        return $s;
    }

    /**
     * 输出异常信息
     * @param $message  string  异常信息
     * @param $file     string  异常文件名
     * @param $line     int     异常所在行
     * @param $traceStr string  异常追踪信息
     */
    public static function ExceptionMessage($message, $file, $line, $trace)
    {
        include MIN_PATH . 'Tpl/ExceptionMessage.php';
    }

    /**
     * 输出显示错误提示
     * @param $message  string  错误信息
     * @param $file     string  文件
     * @param $line     integer 行号
     */
    public static function SysErrorMessage($message, $file, $line)
    {
        include MIN_PATH . 'Tpl/SysErrorMessage.php';
    }

    /**
     * 数组转换成HTML代码 (支持双行变色)
     * @param array $arr 一维数组
     * @param int $type 显示类型
     * @param boolean $html 是否转换为 HTML 实体
     * @return string
     */
    public static function arr2str($arr, $type = 2, $html = TRUE)
    {
        $s = '';
        $i = 0;
        foreach ($arr as $k => $v) {
            switch ($type) {
                case 0:
                    $k = '';
                    break;
                case 1:
                    $k = "#$k ";
                    break;
                default:
                    $k = "#$k => ";
            }

            $i++;
            $c = $i % 2 == 0 ? ' class="even"' : '';
            $html && is_string($v) && $v = htmlspecialchars($v);
            if (is_array($v) || is_object($v)) {
                $v = gettype($v);
            }
            $s .= "<li$c>$k$v</li>";
        }
        return $s;
    }
}