<?php
/**
 * User: Peter Wang
 * Date: 16/9/13
 * Time: 上午9:09
 */

namespace Trendi\Foundation;

use Trendi\Support\Arr;
use Trendi\Support\Dir;
use Trendi\Config\Config;
use Trendi\Http\Response;
use Trendi\Http\Request;
use Trendi\Mvc\AssignData;
use Trendi\Mvc\Template;

class Controller
{

    /**
     * @var \Trendi\Http\View;
     */
    public $view;

    /**
     * @var \Trendi\Http\Request
     */
    protected $request = null;

    /**
     * @var \Trendi\Http\Response
     */
    protected $response = null;


    public function __construct(Request $request, Response $response)
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
     * @throws \Trendi\Mvc\Exception\InvalidArgumentException
     */
    public function render($viewPath, $assign = [])
    {
        $viewRoot = Config::get("view.path");
        $theme = Config::get("view.theme");
        $realViewRoot = Dir::formatPath($viewRoot).$theme;
        Template::setViewRoot($realViewRoot);
        Template::setViewCacheRoot(Config::get("view.compile_path"));
        Template::setEngine(Config::get("view.engine"));
        $assign = Arr::merge($assign, $this->view->getAssignData());
        $content = Template::render($viewPath, $assign);
        return $content;
    }
    
}