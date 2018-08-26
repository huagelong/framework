<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 2018/7/31
 * Time: 13:16
 */

namespace Trensy\Server\Swoole;


use Trensy\Event;
use Trensy\Http\RequestAbstract;
use Trensy\Http\ResponseAbstract;

use swoole_http_request as SwooleHttpRequest;


class HttpRequest extends RequestAbstract
{
    protected $swooleHttpRequest;

    public function __construct(SwooleHttpRequest $swooleHttpRequest)
    {
        $this->swooleHttpRequest = $swooleHttpRequest;
    }

    function get()
    {
       return $this->swooleHttpRequest->get?$this->swooleHttpRequest->get:[];
    }

    function post()
    {
        return $this->swooleHttpRequest->post?$this->swooleHttpRequest->post:[];
    }

    function cookie()
    {
        return $this->swooleHttpRequest->cookie?$this->swooleHttpRequest->cookie:[];
    }

    function files()
    {
        return $this->swooleHttpRequest->files?$this->swooleHttpRequest->files:[];
    }

    function server()
    {

        return $this->swooleHttpRequest->server?$this->swooleHttpRequest->server:[];
    }

    function header()
    {
        return $this->swooleHttpRequest->header?$this->swooleHttpRequest->header:[];
    }

    function rawContent()
    {
        return $this->swooleHttpRequest->rawContent();
    }
}