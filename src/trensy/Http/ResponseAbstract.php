<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 2018/7/27
 * Time: 14:29
 */

namespace Trensy\Http;


abstract class ResponseAbstract
{
    abstract function cookie($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false);
    abstract function rawcookie($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false);
    abstract function status($http_status_code);
    abstract function header($key, $value);
    abstract function write($data);
    abstract function end($html);
    abstract function redirect($url);
    abstract function gzip($level = 0);
    abstract function sendfile($filename);
}