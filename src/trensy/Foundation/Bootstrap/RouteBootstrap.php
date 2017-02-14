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
            if(!method_exists($obj, "perform")){
                throw new \Exception(" 'perform' method must defined");
            }
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
                $path = array_isset($v, "path");
                $uses = array_isset($v, "uses");
                $middleware = array_isset($v, "middleware");
                $defaults = array_isset($v, "defaults");
                
                $where = [];
                if (stristr($path, "<")) {
                    $regStr = "\<(.*?)\:([^\>]+)\>";
                    preg_match_all("/{$regStr}/", $path, $matches, PREG_SET_ORDER);
            
                    if ($matches) {
                        foreach ($matches as $match) {
                            $key = array_isset($match, 1);
                            if(stristr($key, "=")){
                                list($key,$_v) = explode("=", $key);
                                $defaults[$key] = $_v;
                            }
                            $value = array_isset($match, 2);
                            if ($key && $value) {
                                $where[$key] = $value;
                            }
                        }
                    }
                    $path = preg_replace_callback("/{$regStr}/", function($match){
                        $key = array_isset($match, 1);
                        if(stristr($key, "=")){
                            list($key,$_v) = explode("=", $key);
                        }
                        return "{".$key."}";
                    },$path);
                }
                $v['path'] = $path;
                if($namespace){
                    $uses = $namespace.$uses;
                }
                $v['uses'] = $uses;
                $v['where'] = $where;
                $v['middleware'] = $middleware;
                $v['defaults'] = $defaults;
                
                $method = isset($v['method'])?$v['method']:[];
                $_method = $method=="*"?"any":$method;
                $_method = $_method?$_method:"any";
                $v['method'] = $method;
                Route::bind($_method, [$v['path'],$v]);
            }
        }
    }

}