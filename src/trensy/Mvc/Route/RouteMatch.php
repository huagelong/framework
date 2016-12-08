<?php
/**
 * 匹配route
 *
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Mvc\Route;

use Trensy\Coroutine\Base\CoroutineScheduler;
use Trensy\Coroutine\Base\CoroutineTask;
use Trensy\Coroutine\Event;
use Trensy\Foundation\Bootstrap\Session;
use Trensy\Http\Request;
use Trensy\Http\Response;
use Trensy\Mvc\Route\Base;
use Trensy\Mvc\Route\Base\Generator\UrlGenerator;
use Trensy\Mvc\Route\Base\Matcher\UrlMatcher;
use Trensy\Mvc\Route\Base\RequestContext;
use Trensy\Mvc\Route\Base\RouteCollection as BaseRouteCollection;
use Trensy\Mvc\Route\Exception\PageNotFoundException;
use Trensy\Support\Arr;

class RouteMatch
{
    protected static $instance = null;
    /**
     * 所有路由数据
     * @var array
     */
    protected static $allRoute = [];

    protected static $collectionInstance = null;

    protected static $middlewareConfig = [];

    /**
     *  instance
     * @return \Trensy\Mvc\Route\RouteMatch
     */
    public static function getInstance()
    {
        if (self::$instance) return self::$instance;

        return self::$instance = new self();
    }

    public function setMiddlewareConfig($middlewareConfig)
    {
        self::$middlewareConfig = $middlewareConfig;
    }

    protected function getAllGroupRoute()
    {
        if (self::$allRoute) return self::$allRoute;
        return self::$allRoute = RouteGroup::getResult();
    }


    protected function getRootCollection()
    {
        if (self::$collectionInstance) return self::$collectionInstance;
        $rootCollection = new BaseRouteCollection();
        $allRoute = $this->getAllGroupRoute();
        if ($allRoute) {
            foreach ($allRoute as $v) {
                $rootCollection->addCollection($v);
            }
        }

        //get Single route collection
        $single = RouteBase::getResult(RouteBase::DEFAULTGROUP_KEY);

        if ($single) {
            foreach ($single as $k => $v) {
                $rootCollection->add($k, $v);
            }
        }

        return self::$collectionInstance = $rootCollection;
    }

    /**
     * 修复首页匹配问题
     * @param $url
     * @return mixed
     */
    protected function groupFilter($url)
    {
        $groupPrefixs = RouteGroup::getGroupPrefixs();
        if(!$groupPrefixs) return $url;
        $urlTmp = ltrim($url,"/");

        if(in_array($urlTmp,$groupPrefixs))
        {
            return $url."/";
        }
        return $url;
    }

    /**
     * 获取匹配数据
     * @param $url
     * @return array
     */
    public function match($url)
    {
        $rootCollection = $this->getRootCollection();
        $context = new RequestContext();
        $context->fromRequest(Request::createFromGlobals());
        $matcher = new UrlMatcher($rootCollection, $context);
        $url = $this->groupFilter($url);
        $parameters = $matcher->match($url);
        return $parameters;
    }


    /**
     * 根据路由名称获取url
     *
     * @param $routeName
     * @param array $params
     * @return string
     */
    public function url($routeName, $params = [])
    {
        $sysCacheKey = md5($routeName . serialize($params));

        $url = syscache()->get($sysCacheKey);

        if ($url) return $url;

        $rootCollection = $this->getRootCollection();
        $context = new RequestContext();
        $context->fromRequest(Request::createFromGlobals());
        $generator = new UrlGenerator($rootCollection, $context);
        $url = $generator->generate($routeName, $params);

        syscache()->set($sysCacheKey, $url, 3600);

        return $url;
    }

    /**
     * http 执行匹配
     *
     * @param $url
     * @return mixed
     * @throws PageNotFoundException
     */
    public function run($url, Request $request, Response $response)
    {
        $this->sessionStart($request, $response);

        $sysCacheKey = md5(__CLASS__ . $url);

        $parameters = syscache()->get($sysCacheKey);
        
        if (!$parameters) {
            $parameters = $this->match($url);
            syscache()->set($sysCacheKey, $parameters, 3600);
        }

        if ($parameters) {
            $secondReq = [];
            foreach ($parameters as $k => $v) {
                if (substr($k, 0, 1) != '_') {
                    $secondReq[$k] = $v;
                }
            }

            $request->overrideGlobals();

            $require = [$request, $response, $secondReq];

            return $this->runBase($require, $parameters);
        }
    }

    /**
     * session start
     *
     * @param $request
     * @param $response
     */
    protected function sessionStart($request, $response)
    {
        $session = new Session();
        $session->start($request, $response);
    }


    /**
     * socket 执行匹配
     *
     * @param $url
     * @param array $requestData
     * @param server
     * @return string
     * @throws PageNotFoundException
     */
    public function runSocket($url, $requestData = [], $serv, $fd)
    {
        $sysCacheKey = md5($url);

        $parameters = syscache()->get($sysCacheKey);

        if (!$parameters) {
            $parameters = $this->match($url);
            syscache()->set($sysCacheKey, $parameters, 3600);
        }

        if ($parameters) {
            $require = [];
            foreach ($parameters as $k => $v) {
                if (substr($k, 0, 1) != '_') {
                    $require[$k] = $v;
                }
            }

            if ($requestData) $require = Arr::merge($require, $requestData);

            return $this->runBase($require, $parameters, [$serv, $fd]);
        }
        return "";
    }

    /**
     * 基础执行匹配
     *
     * @param $require
     * @param $parameters
     * @return mixed
     * @throws PageNotFoundException
     */
    private function runBase($require, $parameters, $otherData = null)
    {
        if ($parameters) {
            $controller = isset($parameters['_controller']) ? $parameters['_controller'] : null;
            if ($controller) {
                $middleware = isset($parameters['_middleware']) ? $parameters['_middleware'] : null;

                if ($middleware) {
                    $midd = self::$middlewareConfig;
                    if ($midd) {
                        if(is_array($middleware)){
                            foreach ($middleware as $v) {
                                if (isset($midd[$v])) {
                                    $class = $midd[$v];
                                    $obj = new $class();
                                    $rs = call_user_func_array([$obj, "perform"], $require);
                                    if (!$rs) return;
                                }
                            }
                        }elseif(is_string($middleware)){
                            $class = $midd[$middleware];
                            $obj = new $class();
                            $rs = call_user_func_array([$obj, "perform"], $require);
                            if (!$rs) return;
                        }
                    }
                }
                if ($controller instanceof \Closure) {
                    $generator = call_user_func($controller, $require);
                    if ($generator instanceof \Generator) {
                        $task = new CoroutineTask($generator);
                        $task->work($task->getRoutine());
                        unset($task);
                    }
                } elseif (is_string($controller)) {
                    if (stristr($controller, "@")) {
                        list($controller, $action) = explode("@", $controller);
                        //如果是http服务器
                        if (isset($require[0]) && ($require[0] instanceof Request)) {
                            $obj = new $controller($require[0], $require[1]);
                            $content = call_user_func_array([$obj, $action], $require[2]);
                        } else {
                            //tcp
                            list($serv, $fd) = $otherData;
                            $obj = new $controller($serv, $fd);
                            $content = call_user_func_array([$obj, $action], $require);
                        }
                        if ($content instanceof \Generator) {
//                            $scheduler = new CoroutineScheduler();
//                            $scheduler->newTask($content);
//                            $scheduler->run();
                            $task = new CoroutineTask($content);
                            $task->work($task->getRoutine());
                            unset($task);
                        }
                        Event::fire("clear");
                    } else {
                        throw new PageNotFoundException("page not found!");
                    }
                } else {
                    throw new PageNotFoundException("page not found!");
                }
            } else {
                throw new PageNotFoundException("page not found!");
            }
        }
    }

    public function __destruct()
    {
    }
}