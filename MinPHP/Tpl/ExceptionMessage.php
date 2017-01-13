<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>出错啦！</title>
    <style type="text/css">
        body, div, ul, li, h1 {
            margin: 0;
            padding: 0
        }

        .content h1, .content ul, .content ul li, .content ul li span, .content ul table tr td {
            font: 14px/1.6 'Microsoft YaHei', Verdana, Arial, sans-serif
        }

        .content {
            width: 98%;
            margin: 8px auto;
            overflow: hidden;
            color: #000;
            border-radius: 5px;
            box-shadow: 0 0 20px #555;
            background: #fff;
            min-width: 300px
        }

        .content h1 {
            font-size: 18px;
            height: 26px;
            line-height: 26px;
            padding: 10px 3px 0;
            border-bottom: 1px solid #dbdbdb;
            font-weight: 700
        }

        .content ul, .content h1 {
            width: 98%;
            margin: 0 auto;
            overflow: hidden
        }

        .content ul {
            list-style: none;
            padding: 3px;
            word-break: break-all
        }

        .content ul li, .content ul table tr td {
            padding: 0 3px
        }

        .content ul li span {
            float: left;
            display: inline;
            width: 70px
        }

        .content ul li.even {
            background: #ddd
        }

        .content .fo {
            border-top: 1px solid #dbdbdb;
            padding: 5px 3px 10px;
            color: #666;
            text-align: right
        }
    </style>
</head>
<body style="background:#aaa;padding:8px 0">
<div class="content">
    <h1>错误信息</h1>
    <ul>
        <li><span>消息:</span> <font color="red"><?php echo $message; ?></font></li>
        <li><span>文件:</span> <?php echo $file; ?></li>
        <li><span>位置:</span> 第 <?php echo $line; ?> 行</li>
    </ul>

    <h1>错误位置</h1>
    <ul><?php echo self::GetExceptionFileCode($file, $line); ?></ul>

    <h1>程序流程</h1>
    <ul><?php echo self::arr2str(explode("\n", $trace), 0); ?></ul>

    <h1>SQL</h1>
    <ul><?php echo self::arr2str($_ENV['_sqls'], 1, FALSE); ?></ul>

    <h1>$_GET</h1>
    <ul><?php echo self::arr2str($_GET); ?></ul>

    <h1>$_POST</h1>
    <ul style="white-space:pre"><?php echo print_r(arr_html($_POST), 1); ?></ul>

    <h1>$_COOKIE</h1>
    <ul><?php echo self::arr2str($_COOKIE); ?></ul>

    <h1>包含文件</h1>
    <ul><?php echo self::arr2str(get_included_files(), 1); ?></ul>

    <h1>其他信息</h1>
    <ul>
        <li><span>请求路径:</span> <?php echo $_SERVER['REQUEST_URI']; ?></li>
        <li><span>当前时间:</span> <?php echo date('Y-m-d H:i:s', $_ENV['_time']); ?></li>
        <li><span>客户端IP:</span> <?php echo $_ENV['_ip']; ?></li>
        <li><span>运行时间:</span> <?php echo run_time(); ?></li>
        <li><span>内存开销:</span> <?php echo run_mem(); ?></li>
    </ul>

    <ul class="fo"><?= 'MinPHP：' . MIN_VERSION ?></ul>
</div>
</body>
</html>
