<?php
/**
 *  默认http请求
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */

namespace Trensy\Foundation\Bootstrap\Http;


use Trensy\Http\RequestAbstract;

class Request extends RequestAbstract
{

    public function __construct()
    {
        if (@get_magic_quotes_gpc()) {
            $_GET = $this->sec($_GET);
            $_POST = $this->sec($_POST);
            $_COOKIE = $this->sec($_COOKIE);
            $_FILES = $this->sec($_FILES);
        }
        $_SERVER = $this->sec($_SERVER);
    }

    public function get()
    {
       return $_GET;
    }

    public function post()
    {
        return $_POST;
    }

    public function cookie()
    {
        return $_COOKIE;
    }

    public function files()
    {
        return $_FILES;
    }

    public function server()
    {
        return $_SERVER;
    }

    public function header()
    {
        return [];
    }

    public function rawContent()
    {
       return file_get_contents("php://input");
    }

    protected function sec(&$array)
    {
        // 如果是数组，遍历数组，递归调用
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                $array[$k] = $this->sec($v);
            }
        } else
            if (is_string($array)) {
                // 使用addslashes函数来处理
                $array = addslashes($array);
            } else
                if (is_numeric($array)) {
                    $array = intval($array);
                }
        return $array;
    }
}