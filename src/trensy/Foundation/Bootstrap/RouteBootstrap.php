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
 * @version         3.0.0
 */

namespace Trensy\Foundation\Bootstrap;

use Trensy\Di;
use Trensy\Mvc\Route\Route;
use Trensy\Config;
use Trensy\Shortcut;
use Trensy\Support\Dir;

class RouteBootstrap
{
    use Shortcut;

    protected static $instance = null;

    /**
     *  instance
     * @return \Trensy\Foundation\Bootstrap\RouteBootstrap
     */
    public static function getInstance($configKey='route')
    {
        if (self::$instance[$configKey]) return self::$instance[$configKey];

        return self::$instance[$configKey] = new self($configKey);
    }

    /**
     * constructor.
     */
    public function __construct($configKey)
    {
        $this->loadFromConfig($configKey);
    }

    /**
     * 通过配置加载route
     */
    public function loadFromConfig($configKey)
    {
        $config = Config::get($configKey);
        //route 方式自定义
        $myRoute = Config::get("app.".$configKey);
        if($myRoute){
            $obj = Di::get($myRoute);
//            $obj = new $myRoute;
            if(!method_exists($obj, "perform")){
                throw new \Exception(" 'perform' method must defined");
            }
            $config = $obj->perform($config);
        }else{
            $obj = Di::get(\Trensy\Mvc\Route\RouteHandle::class);
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
        $namespace = $this->array_isset($config, "namespace");
        if($routes){
            foreach ($routes as $v){
                $path = $this->array_isset($v, "path");
                $uses = $this->array_isset($v, "uses");
                $middleware = $this->array_isset($v, "middleware");
                $defaults = $this->array_isset($v, "defaults");
                
                $where = [];
                if (stristr($path, "<")) {
                    $regStr = "\<(.*?)\:([^\>]+)\>";
                    preg_match_all("/{$regStr}/", $path, $matches, PREG_SET_ORDER);
            
                    if ($matches) {
                        foreach ($matches as $match) {
                            $key = $this->array_isset($match, 1);
                            if(stristr($key, "=")){
                                list($key,$_v) = explode("=", $key);
                                $defaults[$key] = $_v;
                            }
                            $value = $this->array_isset($match, 2);
                            if ($key && $value) {
                                $where[$key] = $value;
                            }
                        }
                    }
                    $path = preg_replace_callback("/{$regStr}/", function($match){
                        $key = $this->array_isset($match, 1);
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