<?php
/**
 *  di setting
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Foundation\Bootstrap\Config;


class AliasConfig
{

    public static function getOptions()
    {
        return [
            "Di" => \Trensy\Di\Di::class,
            "RunMode" => \Trensy\Support\RunMode::class,
            "Arr" => \Trensy\Support\Arr::class,
            "Dir" => \Trensy\Support\Dir::class,
            "Helper" => \Trensy\Support\Helper::class,
            "Config" => \Trensy\Config\Config::class,
            "Route" => \Trensy\Mvc\Route\Route::class,
            "Context" => \Trensy\Server\Facade\Context::class,
            "Task" => \Trensy\Server\Facade\Task::class,
            "Job" => \Trensy\Foundation\Bootstrap\Facade\Job::class,
            "Log" => \Trensy\Foundation\Bootstrap\Facade\Log::class,
            "Session" => \Trensy\Foundation\Bootstrap\Facade\Session::class,
            "Controller"=>\Trensy\Foundation\Controller::class,
            "RpcController"=>\Trensy\Rpc\Controller::class,
            "WSSController"=>\Trensy\Server\WebSocket\WSServer::class,
            "WSClient"=>\Trensy\Server\WebSocket\WSClient::class,
            "Lang"=>\Trensy\Support\Lang::class,
            "Flash"=>\Trensy\Support\Flash::class,
            "Timer"=>\Trensy\Support\Timer::class,
        ];
    }

}