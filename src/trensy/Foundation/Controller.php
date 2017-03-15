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
use Trensy\Mvc\View\Engine\Bladex\Factory;
use Trensy\Support\Arr;
use Trensy\Support\Dir;
use Trensy\Support\ElapsedTime;
use Trensy\Support\RunMode;
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
        
        $bladexEx = Config::get("server.httpd.server.view.bladex_ex");
        //执行环境
        $version = Config::get("server.httpd.server.view.static_version");
        $runMode = RunMode::getRunMode();
        $config = [$version, $bladexEx, $runMode];
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
        $content = Tool::myJsonEncode($result);
        $this->response->end($content, $useZip);
    }

}