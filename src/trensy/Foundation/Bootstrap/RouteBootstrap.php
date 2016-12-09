<?php
/** 
 * 路由初始化
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

namespace Trensy\Foundation\Bootstrap;

use Route;
use Trensy\Config\Config;
use Trensy\Support\Dir;

class RouteBootstrap
{
    protected static $instance = null;

    /**
     *  instance
     * @return \Trensy\Foundation\Bootstrap\RouteBootstrap
     */
    public static function getInstance()
    {
        if (self::$instance) return self::$instance;

        return self::$instance = new self();
    }

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->loadFromConfig();
    }

    /**
     * 通过配置加载route
     */
    public function loadFromConfig()
    {
        $config = Config::get("route");
        //route 方式自定义
        $myRoute = Config::get("app.route");
        if($myRoute){
            $obj = new $myRoute;
            $config = $obj->perform($config);
        }
        if ($config) {
            foreach ($config as $value){
                $this->loadOneRouteConfig($value);
            }
        }
    }

    /**
     * group single 处理
     *
     * @param $config
     */
    private function loadOneRouteConfig($config)
    {
        $isGroup = false;

        if((isset($config['name']) && $config['name']) ||
            (isset($config['prefix']) && $config['prefix']) ||
            (isset($config['domain']) && $config['domain']) ||
            (isset($config['middleware']) && $config['middleware']) ||
            (isset($config['methods']) && $config['methods'])
        ){
            $isGroup = true;
            if(!isset($config['name']) || !$config['name']){
                $config['name'] = md5(serialize($config));
            }
        }

        if($isGroup){
            Route::group($config,function() use($config){
                $this->loadSingle($config);
            });
        }else{
            $this->loadSingle($config);
        }
    }

    /**
     * single 处理
     * @param $config
     */
    private function loadSingle($config)
    {
        $routes = isset($config['routes'])?$config['routes']:[];
        $namespace = array_isset($config, "namespace");
        if($routes){
            foreach ($routes as $v){
                if(!isset($v['path'])){
                    $method = array_isset($v, 0);
                    $path = array_isset($v, 1);
                    $uses = array_isset($v, 2);
                    $name = array_isset($v, 3);
                    $middleware = array_isset($v, 4);
                    $domain = array_isset($v, 5);

                    $v['method'] = $method;
                    $where = [];
                    if (stristr($path, "{")) {
                        $regStr = "\{(.*?)\:([^\}]+)\}";
                        preg_match_all("/{$regStr}/", $path, $matches, PREG_SET_ORDER);
                        if ($matches) {
                            foreach ($matches as $match) {
                                $key = array_isset($match, 1);
                                $value = array_isset($match, 2);
                                if ($key && $value) {
                                    $where[$key] = $value;
                                }
                            }
                        }
                        $path = preg_replace("/{$regStr}/", '{$1}', $path);
                    }
                    $v['path'] = $path;
                    if($namespace){
                        $uses = $namespace.$uses;
                    }
                    $v['uses'] = $uses;
                    $v['name'] = $name;
                    $v['where'] = $where;
                    $v['domain'] = $domain;
                    $v['middleware'] = $middleware;
                }
                $method = isset($v['method'])?$v['method']:[];
                $_method = $method=="*"?"any":$method;
                $_method = $_method?$_method:"any";
                Route::bind($_method, [$v['path'],$v]);
            }
        }
    }

}