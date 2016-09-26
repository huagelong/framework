<?php
/**
 * User: Peter Wang
 * Date: 16/9/13
 * Time: 下午6:29
 */

namespace Trendi\Http;

use swoole_http_response as SwooleHttpResponse;
use Trendi\Config\Config;
use Trendi\Http\Exception\ContextErrorException;
use Trendi\Mvc\Controller as MvcController;
use Trendi\Support\Arr;

class Response
{
    private $response;
    private $hasEnd = 0;
    protected $headerStack = [];

    /**
     * @var \Trendi\Http\View;
     */
    public $view;

    public function __construct(SwooleHttpResponse $response)
    {
        $this->view = new View();
        $this->response = $response;
    }


    public function cookie($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false)
    {
        return $this->response->cookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

    public function rawcookie($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false)
    {
        return $this->response->rawcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

    public function status($http_status_code)
    {
        return $this->response->status($http_status_code);
    }

    public function gzip($level = 1)
    {
        return $this->response->gzip($level);
    }

    public function header($key, $value)
    {
        $this->headerStack[$key] = $value;
    }

    public function write($data)
    {
        return $this->response->write($data);
    }

    public function end($html = '')
    {
        if ($this->hasEnd) {
            throw new ContextErrorException("http has send");
        }
        $this->hasEnd = 1;
        if ($this->headerStack) {
            foreach ($this->headerStack as $k => $v) {
                $this->response->header($k, $v);
            }
        }
        $data = $this->response->end($html);

        return $data;
    }

    public function sendfile($filename)
    {
        if ($this->headerStack) {
            foreach ($this->headerStack as $k => $v) {
                $this->response->header($k, $v);
            }
        }
        return $this->response->sendfile($filename);
    }

    public function redirect($url)
    {
        $this->header("Location", $url);
        $this->status(302);
        return $this->end('');
    }

    public function render($viewPath, $assign = [])
    {
        MvcController::setViewRoot(Config::get("view.path"));
        MvcController::setViewCacheRoot(Config::get("view.compile_path"));
        MvcController::setEngine(Config::get("view.engine"));
        $assign = Arr::merge($assign, $this->view->getAssignData());
        $content = MvcController::render($viewPath, $assign);
        return $content;
    }

}