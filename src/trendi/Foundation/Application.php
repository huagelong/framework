<?php
/**
 * 项目初始入口
 * 
 * User: Peter Wang
 * Date: 16/9/8
 * Time: 上午10:45
 */

namespace Trendi\Foundation;

use Trendi\Console\Application as CmdApplication;
use Trendi\Config\Config as CConfig;
use Trendi\Foundation\Bootstrap\Bootstrap;
use Trendi\Foundation\Bootstrap\PoolBootstrap;
use Trendi\Foundation\Bootstrap\RouteBootstrap;
use Trendi\Foundation\Command;
use Trendi\Mvc\Route\RouteMatch;
use Trendi\Support\Dir;
use Trendi\Support\Arr;
use Trendi\Support\Log;


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
        $this->initRelease();
        RouteBootstrap::getInstance();
    }

    protected function initRelease()
    {
        $release = CConfig::get("app.view.fis.compile_path");
        if(is_dir($release)){
           CConfig::set("_release.path", $release);
        }
    }

    /**
     * rpc server 初始化
     */
    public function rpcBootstrap()
    {
        RouteBootstrap::getInstance();
    }

    /**
     * 连接池初始化
     */
    public function poolBootstrap()
    {
        PoolBootstrap::getInstance();
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
            new Command\Rpc\Start(),
            new Command\Rpc\Restart(),
            new Command\Rpc\Status(),
            new Command\Rpc\Stop(),
            new Command\Pool\Start(),
            new Command\Pool\Restart(),
            new Command\Pool\Status(),
            new Command\Pool\Stop(),
            new Command\Job\Start(),
            new Command\Job\Restart(),
            new Command\Job\Status(),
            new Command\Job\Stop(),
            new Command\Job\Clear(),
            new Command\Server\Start(),
            new Command\Server\Restart(),
            new Command\Server\Status(),
            new Command\Server\Stop(),
            new Command\Artisan\Optimize(),
            new Command\Artisan\Clean(),
        ];
        $config = CConfig::get("app.command");
        if ($config) {
            $commands = Arr::merge($commands, $config);
        }
        $application = new CmdApplication();
        foreach ($commands as $v) {
            $application->add($v);
        }
       
        $application->run();
    }

}