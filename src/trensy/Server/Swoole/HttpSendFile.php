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

namespace Trensy\Server\Swoole;

use Trensy\Config;
use Trensy\Shortcut;
use Trensy\Http\Request;
use Trensy\Http\Response;

class HttpSendFile
{
    use Shortcut;

    protected $request = null;
    protected $response = null;
    protected $config = null;
    protected $analyse = [];
    protected $expire_time = 86400;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->config = Config::get("swoole.httpd");
    }

    public function getAnalyse()
    {
        return $this->analyse;
    }

    /**
     *  发送静态文件数据
     *
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
            $this->response->header('Access-Control-Allow-Origin', "*");
            $this->response->header('Access-Control-Allow-Methods', 'GET');
            $this->response->header('Access-Control-Allow-Headers:', "x-requested-with,content-type");
            $this->response->header("Content-Type", $mime);
            $this->response->sendfile($filePath);
//            $data = file_get_contents($filePath);
//            echo $data;
//            $this->response->end($data);
        } else {
            $this->response->end();
        }
    }

    /**
     * 文件分析
     *
     * @return array
     */
    public function analyse()
    {
        if ($this->analyse) return $this->analyse;

        $pathinfo = $this->request->getPathInfo();

        $sysCacheKey = __CLASS__.__METHOD__.md5($pathinfo);
        $analyse = $this->syscache()->get(__CLASS__.$sysCacheKey);
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
        $mimeMap = $this->array_isset($mime,$extension);
        $this->analyse = [$isFile, $filePath, $extension, $mimeMap, $notFound];
        $this->syscache()->set($sysCacheKey, $this->analyse);

        return $this->analyse;
    }

}