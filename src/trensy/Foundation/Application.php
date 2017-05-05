<?php
/**
 * 项目初始入口
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

namespace Trensy\Foundation;

use Trensy\Console\Application as CmdApplication;
use Trensy\Config\Config as CConfig;
use Trensy\Foundation\Bootstrap\Bootstrap;
use Trensy\Foundation\Bootstrap\RouteBootstrap;
use Trensy\Foundation\Command;
use Trensy\Mvc\Route\RouteMatch;
use Trensy\Support\Dir;
use Trensy\Support\Arr;
use Trensy\Support\Log;


class Application
{

    /**
     * 项目路径
     *
     * @var string
     */
    protected static $rootPath = null;

    public function __construct($rootPath)
    {
        self::$rootPath = Dir::formatPath($rootPath);
    }
    
    public function httpBoostrap()
    {
        RouteBootstrap::getInstance();
    }

    /**
     * https server 路由开始匹配
     *
     * @param $request
     * @param $response
     * @return mixed
     */
    public function start($request, $response)
    {
        $url = $request->getPathInfo();
        //pathinfo 处理
        $pathinfoMiddle = CConfig::get("app.pathinfo");
        if($pathinfoMiddle){
            $obj = new $pathinfoMiddle;
            if(!method_exists($obj, "perform")){
                throw new \Exception(" 'perform' method must defined");
            }
            $url = $obj->perform($url);
        }
        $routeObj = RouteMatch::getInstance();
       
        $middlewareConfig = CConfig::get("app.middleware");
        
        $routeObj->setMiddlewareConfig($middlewareConfig);
    
        $resut = $routeObj->run($url, $request, $response);
        return $resut;
    }

    /**
     * 获取项目根目录
     *
     * @return string
     */
    public static function getRootPath()
    {
        return self::$rootPath;
    }

    /**
     * Command 初始化
     *
     * @throws \Exception
     */
    public static function runCmd()
    {
        $commands = [
            new Command\Httpd\Start(),
            new Command\Httpd\Restart(),
            new Command\Httpd\Status(),
            new Command\Httpd\Stop(),
            new Command\Httpd\Reload(),
            new Command\Server\Start(),
            new Command\Server\Restart(),
            new Command\Server\Status(),
            new Command\Server\Stop(),
            new Command\Server\Reload(),
            new Command\Artisan\Optimize(),
            new Command\Artisan\Dbsync(),
            new Command\Artisan\BuildPhar(),
            new Command\Artisan\Dbdiff(),
            new Command\Artisan\Mysqldiff()
        ];

        $config = CConfig::get("app.command");
        if ($config) {
            $commandsTmp = [];
            foreach ($config as $cv){
                $commandsTmp[] = new $cv;
            }
            $commands = array_merge($commands, $commandsTmp);
        }

        $application = new CmdApplication();
        foreach ($commands as $v) {
            $application->add($v);
        }

        $application->run();
    }

}