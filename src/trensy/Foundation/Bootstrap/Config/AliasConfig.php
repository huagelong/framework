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
 * @version         3.0.0
 */

namespace Trensy\Foundation\Bootstrap\Config;


class AliasConfig
{

    public static function getOptions()
    {
        return [
            "Di" => \Trensy\Di::class,
            "Arr" => \Trensy\Support\Arr::class,
            "Dir" => \Trensy\Support\Dir::class,
            "Config" => \Trensy\Config::class,
            "Route" => \Trensy\Mvc\Route\Route::class,
            "Context" => \Trensy\Context::class,
            "Log" => \Trensy\Foundation\Bootstrap\Facade\Log::class,
            "Session" => \Trensy\Foundation\Bootstrap\Facade\Session::class,
            "Controller"=>\Trensy\Controller::class,
            "Lang"=>\Trensy\Lang::class,
            "Flash"=>\Trensy\Flash::class,
        ];
    }

}