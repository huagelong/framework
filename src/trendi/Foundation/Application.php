<?php
/**
 *
 * User: Peter Wang
 * Date: 16/9/8
 * Time: 上午10:45
 */

namespace Trendi\Foundation;

use Illuminate\Support\Arr;
use Symfony\Component\Console\Application as CmdApplication;
use Trendi\Config\Config as CConfig;
use Trendi\Foundation\Bootstrap\Bootstrap;
use Trendi\Foundation\Bootstrap\PoolBootstrap;
use Trendi\Foundation\Bootstrap\RouteBootstrap;
use Trendi\Foundation\Command;
use Trendi\Mvc\Route\RouteMatch;
use Trendi\Support\Dir;

class Application
{
    /**
     * frame work version
     */
    const VERSION = '1.0';

    protected static $rootPath = null;

    public function __construct($rootPath)
    {
        self::$rootPath = Dir::formatPath($rootPath);
    }

    public function bootstrap()
    {
        Bootstrap::getInstance(self::$rootPath);
        RouteBootstrap::getInstance();
    }


    public function poolBootstrap()
    {
        Bootstrap::getInstance(self::$rootPath);
        PoolBootstrap::getInstance();
    }

    public function start($request, $response)
    {
        $url = $request->getPathInfo();
        return RouteMatch::getInstance()->run($url, $request, $response);
    }

    public static function getRootPath()
    {
        return self::$rootPath;
    }

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
        ];
        $config = CConfig::get("command");
        if ($config) {
            $commands = Arr::merge($commands, $config);
        }
        $application = new CmdApplication();
        foreach ($commands as $v) {
            $application->add($v);
        }
        $application->run();
    }

    public function __destruct()
    {
    }

}