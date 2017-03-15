<?php
/**
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Server;

use Trensy\Http\Request;
use Trensy\Http\Response;
use Trensy\Server\Exception\InvalidArgumentException;
use Trensy\Support\RunMode;

class HttpSendFile
{

    protected $request = null;
    protected $response = null;
    protected $config = null;
    protected $analyse = [];
    protected $expire_time = 86400;

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function getAnalyse()
    {
        return $this->analyse;
    }

    /**
     *  发送静态文件数据
     * 
     * @throws InvalidArgumentException
     * @throws \Trensy\Http\Exception\ContextErrorException
     */
    public function sendFile()
    {

        list($isFile, $filePath, , $mime, $notFound) = $this->analyse();

        if (!$isFile) return;

        if ($notFound) {
            $this->response->header("Content-Type", $mime);
            $this->response->status(404);
            $this->response->end();
            return;
        }

        $fstat = stat($filePath);

        $readFile = true;

        $expireTime = isset($this->config["static_expire_time"]) ? $this->config["static_expire_time"] : $this->expire_time;
        //过期控制信息
        $ifModifiedSince = $this->request->headers->get('if-modified-since');
        if ($ifModifiedSince) {
            $lastModifiedSince = strtotime($ifModifiedSince);
            if ($lastModifiedSince and $fstat['mtime'] <= $lastModifiedSince) {
                //不需要读文件了
                $readFile = false;
                $this->response->status(304);
            }
        } else {
            $this->response->header('Cache-Control', "max-age={$expireTime}");
            $this->response->header('Pragma', "max-age={$expireTime}");
            $this->response->header('Last-Modified', date('D, d-M-Y H:i:s T', $fstat['mtime']));
            $this->response->header('Expires', "max-age={$expireTime}");
        }

        if ($readFile) {
            $this->response->header("Content-Type", $mime);
            //bug macos sendfile slow
            if(strtolower(PHP_OS) == 'darwin'){
                $data = file_get_contents($filePath);
                $this->response->end($data);
            }else{
                $this->response->sendfile($filePath);
            }
        } else {
            $this->response->end();
        }
    }

    /**
     * 文件分析
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function analyse()
    {
        if ($this->analyse) return $this->analyse;

        $pathinfo = $this->request->getPathInfo();

        $sysCacheKey = md5($pathinfo);

        $analyse = syscache()->get(__CLASS__.$sysCacheKey);
        if($analyse){
            return $this->analyse = $analyse;
        }

        $arr = pathinfo($pathinfo);
        $extension = isset($arr['extension']) ? $arr['extension'] : '';
        $isFile = 0;
        $filePath = "";
        $notFound = 0;
        if ($extension === '') {
            $extension = 'php';
            return $this->analyse = [$isFile, $filePath, $extension, '', $notFound];
        }

        $staticPath = "";
        $staticPathConfig = isset($this->config["static_path"]) ? $this->config["static_path"] : "";
        if(is_array($staticPathConfig)){
            foreach ($staticPathConfig as $k=>$v){
                if(preg_match("/".$k."/", $pathinfo)){
                    $staticPath = dirname($v);
                    break;
                }
            }
        }else{
            $staticPath = dirname($staticPathConfig);
        }
        $filePath = $staticPath . $pathinfo;
        $mime = Mime::get();
        if (is_file($filePath) && isset($mime[$extension])) {
            $isFile = 1;
        }else{
            if ($pathinfo == "/favicon.ico") {
                $isFile = 1;
            }
            $notFound = 1;
        }
        $mimeMap = array_isset($mime,$extension);
        $this->analyse = [$isFile, $filePath, $extension, $mimeMap, $notFound];

        syscache()->set($sysCacheKey, $this->analyse, 3600);

        return $this->analyse;
    }

}