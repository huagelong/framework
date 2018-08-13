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

namespace Trensy;

use Trensy\Config;
use Trensy\Di;
use Trensy\Http\Request;
use Trensy\Http\Response;
use Trensy\Mvc\Template;
use Trensy\Support\Arr;
use Trensy\Support\Dir;
use Trensy\Support\ElapsedTime;
use Trensy\Support\Tool;
use Trensy\Foundation\AnnotationLoadInterface;
use Trensy\Shortcut;

class Controller implements AnnotationLoadInterface
{
    use Shortcut;

    const RESPONSE_SUCCESS_CODE = 200;
    const RESPONSE_NORMAL_ERROR_CODE = 500;

    /**
     * @var \Trensy\Http\AssignData;
     */
    public $view;

    /**
     * @var \Trensy\Http\Request
     */
    public $request = null;

    /**
     * @var \Trensy\Http\Response
     */
    public $response = null;

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Response
     */
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

        $viewRoot = Config::get("app.view.path");
        $realViewRoot = Dir::formatPath($viewRoot);

        $diyView = Config::get("app.view.diy");
        if($diyView){

            $obj = Di::get($diyView);
//            $obj = new $diyView;
            if(!method_exists($obj, "perform")){
                throw new \Exception(" 'perform' method must defined");
            }
            $diyViewRoot = $obj->perform(get_class($this));
            if($diyViewRoot) $realViewRoot = Arr::merge($realViewRoot, $diyViewRoot);
        }

        $template = new Template();
        $template->setViewRoot($realViewRoot);
        $viewCachePath = Config::get("app.view.compile_path");

        $template->setViewCacheRoot($viewCachePath);
        if(isset($this->view) && $this->view) $assign = Arr::merge($assign, $this->view->getAssignData());

        $bladexEx = Config::get("app.view.bladex_ex");
        //执行环境
        $version = Config::get("app.view.static_version");
        $widget = Config::get("app.view.widget");
        $bundleConfig = Config::get("app.view.bundle");

        $config = [$version, $bladexEx, $widget, $bundleConfig];
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
        $this->responseEnd(function() {
            $this->response->setHasEnd(0);
        });
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
        $result['message'] = $errorMsg;
        $result['elapsedTime'] = $elapsedTime;
        $this->response->header("Content-type", "application/json");
        //JSON_NUMERIC_CHECK
        $content = Tool::myJsonEncode($result);
        $this->response->end($content, $useZip);
    }

}