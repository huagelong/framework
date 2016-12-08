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


class DiConfig
{

    public static function getOptions()
    {
        return [
            "context" => [
                "class" => \Trensy\Server\Context::class
            ],
            "task" => [
                "class" => \Trensy\Server\Task::class
            ],
            "job" => [
                "class" => \Trensy\Foundation\Bootstrap\JobBootstrap::class
            ],
            "log" => [
                "class" => \Trensy\Support\Log::class
            ],
            "session" => [
                "class" => \Trensy\Foundation\Bootstrap\Session::class,
            ],
        ];
    }

}