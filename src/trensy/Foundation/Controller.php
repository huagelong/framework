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

namespace Trensy\Foundation;

use Trensy\Config\Config;
use Trensy\Http\Request;
use Trensy\Http\Response;
use Trensy\Mvc\AssignData;
use Trensy\Mvc\Template;
use Trensy\Support\Arr;
use Trensy\Support\Dir;
use Trensy\Support\ElapsedTime;
use Trensy\Support\Tool;

class Controller
{

    const RESPONSE_SUCCESS_CODE = 200;
    const RESPONSE_NORMAL_ERROR_CODE = 500;

    /**
     * @var \Trensy\Http\View;
     */
    public $view;

    /**
     * @var \Trensy\Http\Request
     */
    protected $request = null;

    /**
     * @var \Trensy\Http\Response
     */
    protected $response = null;
    
    protected static $staticMap = null;


    public function __construct(Request $request = null, Response $response = null)
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = new AssignData();
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * 模板render
     *
     * @param $viewPath
     * @param array $assign
     * @return mixed
     * @throws \Trensy\Mvc\Exception\InvalidArgumentException
     */
    public function render($viewPath, $assign = [])
    {
        $viewRoot = Config::get("server.httpd.server.view.path");
        $realViewRoot = Dir::formatPath($viewRoot);

        $diyView = Config::get("server.httpd.server.view.diy");
        if($diyView){
            $obj = new $diyView;
            if(!method_exists($obj, "perform")){
                throw new \Exception(" 'perform' method must defined");
            }
            $diyViewRoot = $obj->perform(get_class($this));
            if($diyViewRoot) $realViewRoot = Arr::merge($realViewRoot, $diyViewRoot);
        }

        $template = new Template();
        $template->setViewRoot($realViewRoot);
        $viewCachePath = Config::get("server.httpd.server.view.compile_path");

        $template->setViewCacheRoot($viewCachePath);
        $assign = Arr::merge($assign, $this->view->getAssignData());
        $assign = Arr::merge($assign, $this->response->view->getAssignData());
        
        $staticPath = rtrim(Config::get("server.httpd.server.static_path"), "/");
        $staticCompilePath = Config::get("server.httpd.server.static_public_path");

        if (!$staticPath) {
            Log::error("server.httpd.server.static_path not set");
            return;
        }

        if (!$staticCompilePath) {
            Log::error("server.httpd.server.static_public_path not set");
            return;
        }

        $staticCompilePath = Dir::formatPath($staticCompilePath);
        $staticMapPath = $staticCompilePath."/static/map.php";
        
        if(!self::$staticMap && is_file($staticMapPath)){
            self::$staticMap = include($staticMapPath);
        }

//        $staticPath = str_replace(Dir::formatPath(ROOT_PATH), "", $staticPath);
        $bladexEx = Config::get("server.httpd.server.view.bladex_ex");
        $config = [self::$staticMap, $bladexEx];
        $template->setConfig($config);

        $content = $template->render($viewPath, $assign);
       
        return $content;
    }

    /**
     * 显示模板
     * @param $viewPath
     * @param array $assign
     */
    public function display($viewPath, $assign = [], $useZip=0)
    {
        $content = $this->render($viewPath, $assign);
        $this->response->end($content, $useZip);
    }


    /**
     * @param $data
     * @param int $errorCode
     * @param string $errorMsg
     */
    public function response($data=[], $errorCode = self::RESPONSE_SUCCESS_CODE, $errorMsg = '', $useZip=0)
    {
        $elapsedTime = ElapsedTime::runtime("sys_elapsed_time");
        $result = [];
        $result['result'] = $data;
        $result['statusCode'] = $errorCode;
        $result['msg'] = $errorMsg;
        $result['elapsedTime'] = $elapsedTime;
        $this->response->header("Content-type", "application/json");
        //JSON_NUMERIC_CHECK
        $content = Tool::my_json_encode($result);
        $this->response->end($content, $useZip);
    }

}