<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 2018/7/31
 * Time: 13:17
 */

namespace Trensy\Server\Swoole;


use Trensy\Event;
use Trensy\Http\ResponseAbstract;
use swoole_http_response as SwooleHttpResponse;
use Trensy\Support\Exception\RuntimeExitException;

class HttpResponse extends ResponseAbstract
{
    protected $swooleHttpResponse;

    public function __construct(SwooleHttpResponse $swooleHttpResponse)
    {
        $this->swooleHttpResponse = $swooleHttpResponse;
    }


    function cookie($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false)
    {
        return $this->swooleHttpResponse->cookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

    function rawcookie($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false)
    {
        return $this->swooleHttpResponse->rawcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

    function status($http_status_code)
    {
        return $this->swooleHttpResponse->status($http_status_code);
    }

    function header($key, $value)
    {
        return $this->swooleHttpResponse->header($key, $value);
    }

    function write($data)
    {
        return $this->swooleHttpResponse->write($data);
    }

    function end($html)
    {
        return $this->swooleHttpResponse->end($html);
    }

    function redirect($url)
    {
        $this->header("Location", $url);
        $this->status(302);
        $this->end('');
        //抛异常中断执行
        throw new RuntimeExitException('redirect->'. $url);
    }

    function gzip($level = 0)
    {
        return $this->swooleHttpResponse->gzip($level);
    }

    function sendfile($filename)
    {
        return $this->swooleHttpResponse->sendfile($filename);
    }

}