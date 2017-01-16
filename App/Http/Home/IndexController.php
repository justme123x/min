<?php
namespace App\Http\Home;

use App\Model\ConfigModel;

/**
 * 测试控制器
 * Class IndexController
 * @author  pengcy123x@foxmail.com
 * @package App\Http\Home
 */
class IndexController
{
    public function Index()
    {
        $model = new ConfigModel();
        $config_list = $model->where(['config_id' => ['>' => 1]])->select();
        $title = '你好';
        include VIEW_PATH . 'index.php';
    }
}
