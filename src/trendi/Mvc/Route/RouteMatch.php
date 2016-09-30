<?php
/**
 * 匹配route
 *
 * User: Peter Wang
 * Date: 16/9/10
 * Time: 下午4:51
 */

namespace Trendi\Mvc\Route;

use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;
use Trendi\Http\Request;
use Trendi\Http\Response;
use Trendi\Mvc\Route\Exception\PageNotFoundException;
use Trendi\Support\Arr;
use Trendi\Coroutine\Event;
use Trendi\Coroutine\SystemCall;

class RouteMatch
{
    protected static $instance = null;
    /**
     * 所有路由数据
     * @var array
     */
    protected static $allRoute = [];

    protected static $collectionInstance = null;
    
    protected static $middlewareConfig=[];

    /**
     *  instance
     * @return \Trendi\Mvc\Route\RouteMatch
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
        $rootCollection = new SymfonyRouteCollection();
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
        $rootCollection = $this->getRootCollection();
        $context = new RequestContext();
        $context->fromRequest(Request::createFromGlobals());
        $generator = new UrlGenerator($rootCollection, $context);
        $url = $generator->generate($routeName, $params);
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
        Event::fire("controller_call_before", [$url, $request, $response]);
        $parameters = $this->match($url);

        if ($parameters) {
            foreach ($parameters as $k => $v) {
                if (substr($k, 0, 1) != '_') {
                    $request->query->set($k, $v);
                }
            }
            $request->overrideGlobals();

            $require = [$request, $response];

            return $this->runBase($require, $parameters);
        }
    }


    /**
     * rpc 执行匹配
     *
     * @param $url
     * @param array $requestData
     * @return string
     * @throws PageNotFoundException
     */
    public function runRpc($url, $requestData = [])
    {
        Event::fire("rpc_controller_call_before", [$url, $requestData]);
        $parameters = $this->match($url);
        if ($parameters) {
            $require = [];
            foreach ($parameters as $k => $v) {
                if (substr($k, 0, 1) != '_') {
                    $require[$k] = $v;
                }
            }

            if ($requestData) $require = Arr::merge($require, $requestData);

            return $this->runBase($require, $parameters);
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
    private function runBase($require, $parameters)
    {
        if ($parameters) {
            $controller = isset($parameters['_controller']) ? $parameters['_controller'] : null;
            if ($controller) {
                $middleware = isset($parameters['_middleware']) ? $parameters['_middleware'] : null;
                if ($middleware) {
                    $midd = self::$middlewareConfig;
                    if($midd){
                        foreach ($middleware as $v){
                            if(isset($midd[$v])){
                                $class = $midd[$v];
                                $obj = new $class();
                                $rs = call_user_func_array([$obj, "perform"], $require);
                                if(!$rs) return ;
                            }
                        }
                    }
                }
                if ($controller instanceof \Closure) {
                    return call_user_func($controller, $require);
                } elseif (is_string($controller)) {
                    if (stristr($controller, "@")) {
                        list($controller, $action) = explode("@", $controller);
                        $content = call_user_func_array([new $controller(), $action], $require);
                        Event::fire("controller_call_after", [$content]);
                        Event::fire("clear");
                        return $content;
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