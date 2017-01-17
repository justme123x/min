<?php
use MinPHP\Base\Router;

Router::group(['namespace' => 'App\Http\Home', 'prefix' => '/admin'], function () {

    Router::get('/ok', 'IndexController@Index');
    Router::get('/news', 'IndexController@aa');
    Router::get('/cc', 'IndexController@cc');
    Router::get('/dd/bb/:num', 'IndexController@Index');

});


Router::dispatch();