<?php
namespace App\Model;
use MinPHP\Driver\Model\Model;

/**
 * 网站配置表
 * @author      zhipeng     <pengcy123x@foxmail.com>
 */
class ConfigModel extends Model
{
    public $table = 'config';
    public $primaryKey = 'config_id';
}