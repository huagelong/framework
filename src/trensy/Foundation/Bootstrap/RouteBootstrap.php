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
        $this->load();
        $this->loadFromConfig();
    }

    /**
     * 路由导入
     *
     * @throws \Trensy\Support\Exception\InvalidArgumentException
     */
    public function load()
    {
        $path = Config::get("route.load_path");

        if ($path) {
            $dir = Dir::formatPath($path);
            if (is_dir($dir)) {
                $configFiles = Dir::glob($dir, '*.php', Dir::SCAN_BFS);

                foreach ($configFiles as $file) {
                    require_once $file;
                }
            }
        }
    }

    /**
     * 通过配置加载route
     */
    public function loadFromConfig()
    {
        $config = Config::get("route.routes");
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
        if($routes){
            foreach ($routes as $v){
                if(!isset($v['path'])&&!isset($v['method'])){
                    list($path,$method,$uses,$name,$where,$domain,$middleware) = $v;
                    $v['method'] = $method;
                    $v['path'] = $path;
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