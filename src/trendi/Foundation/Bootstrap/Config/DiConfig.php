<?php
/**
 *  di setting
 * User: Peter Wang
 * Date: 16/9/8
 * Time: 下午6:08
 */

namespace Trendi\Foundation\Bootstrap\Config;


class DiConfig
{

    public static function getOptions()
    {
        return [
            "context" => [
                "class" => \Trendi\Server\Context::class
            ],
            "task" => [
                "class" => \Trendi\Server\Task::class
            ],
        ];
    }

}