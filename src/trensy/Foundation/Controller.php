<?php
/**
 * User: Peter Wang
 * Date: 16/9/13
 * Time: 上午9:09
 */

namespace Trensy\Foundation;

use Trensy\Di\Di;
use Trensy\Support\Arr;
use Trensy\Support\Dir;
use Trensy\Config\Config;
use Trensy\Http\Response;
use Trensy\Http\Request;
use Trensy\Mvc\AssignData;
use Trensy\Mvc\Template;
use Trensy\Support\Log;

class Controller
{

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


    public function __construct(Request $request=null, Response $response=null)
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = new AssignData();
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

        $fisPath = Config::get("_release.path");
        if($fisPath){
            $fis = Config::get("app.view.fis.view_path");
            $viewRoot = Dir::formatPath($fisPath).$fis;
        }else{
            $viewRoot = Config::get("app.view.path");
        }

        $theme = Config::get("app.view.theme");
        $realViewRoot = Dir::formatPath($viewRoot).$theme;
        Template::setViewRoot($realViewRoot);

        $viewCachePath = Config::get("app.view.compile_path");

        Template::setViewCacheRoot($viewCachePath);
        Template::setEngine(Config::get("app.view.engine"));
        $assign = Arr::merge($assign, $this->view->getAssignData());

        $content = Template::render($viewPath, $assign);
        return $content;
    }

    /**
     * 显示模板
     * @param $viewPath
     * @param array $assign
     */
    public function display($viewPath, $assign = [])
    {
        $content = $this->render($viewPath, $assign);
        $this->response->end($content);
    }
    
}