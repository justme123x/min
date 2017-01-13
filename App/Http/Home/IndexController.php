<?php
namespace App\Http\Home;
class IndexController
{
    public function Index()
    {
        $title = '你好';
        include VIEW_PATH . 'index.php';
    }
}
