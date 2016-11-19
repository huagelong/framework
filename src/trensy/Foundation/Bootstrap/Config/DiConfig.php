<?php
/**
 *  di setting
 * User: Peter Wang
 * Date: 16/9/8
 * Time: 下午6:08
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