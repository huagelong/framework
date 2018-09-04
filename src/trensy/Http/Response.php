<?php
/**
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */

namespace Trensy\Http;

use Trensy\Http\Exception\ContextErrorException;
use Trensy\Support\Exception\RuntimeExitException;
use Trensy\Log;

class Response
{
    public $response;
    public $view;
    private $hasEnd = 0;
    protected $headerStack = [];
    public $gzip = 0;

    /**
     * 初始化
     * Response constructor
     */
    public function __construct(ResponseAbstract $response, $gzip=0)
    {
        $this->response = $response;
        $this->view = new AssignData();
        $this->gzip = $gzip;
    }

    public function setHasEnd($hasEnd)
    {
        $this->hasEnd = $hasEnd;
    }
    
    
    /**
     * 设置cookie
     * @param $key
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return mixed
     */
    public function cookie($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false)
    {
        if($expire>0) $expire = time()+$expire;
        return $this->response->cookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * 设置cookie
     *
     * @param $key
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return mixed
     */
    public function rawcookie($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false)
    {
        $expire = time()+$expire;
        return $this->response->rawcookie($key, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * 设置http code
     * @param $http_status_code
     * @return mixed
     */
    public function status($http_status_code)
    {
        return $this->response->status($http_status_code);
    }

    /**
     * 是否使用gzip 压缩
     * @param int $level
     * @return mixed
     */
    public function gzip($level = 0)
    {
        return $this->response->gzip($level);
    }

    /**
     * header
     * @param $key
     * @param $value
     */
    public function header($key, $value, $isArr=0)
    {
        if(!$isArr){
            $this->headerStack[$key] = $value;
        }else{
            $this->headerStack[$key][] = $value;
        }
    }

    /**
     * @param $str
     */
    public function headerStr($str)
    {
        list($k, $v) = explode(":", $str);
        $this->header($k, $v);
    }

    /**
     * write
     *
     * @param $data
     * @return mixed
     */
    public function write($data)
    {
        return $this->response->write($data);
    }

    /**
     * 输出
     * @param string $html
     * @return mixed
     * @throws ContextErrorException
     */
    public function end($html = '', $useZip = 0)
    {
        if ($this->hasEnd) {
            return Log::sysinfo("http has send");
        }
        $this->hasEnd = 1;
        if ($this->headerStack) {
            foreach ($this->headerStack as $k => $v) {
                if(is_array($v)){
                    foreach ($v as $subV){
                        $this->response->header($k, $subV);
                    }
                }else{
                    $this->response->header($k, $v);
                }
            }
        }
        if($useZip){
            $this->gzip($this->gzip);
        }
        
        $this->response->end($html);
    }

    /**
     * 输出file
     *
     * @param $filename
     * @return mixed
     */
    public function sendfile($filename)
    {
        if ($this->headerStack) {
            foreach ($this->headerStack as $k => $v) {
                if(is_array($v)){
                    foreach ($v as $subV){
                        $this->response->header($k, $subV);
                    }
                }else{
                    $this->response->header($k, $v);
                }
            }
        }
        $this->response->sendfile($filename);
    }

    /**
     * 跳转
     * @param $url
     * @return mixed
     * @throws ContextErrorException
     */
    public function redirect($url)
    {
        $this->response->redirect($url);
    }

}