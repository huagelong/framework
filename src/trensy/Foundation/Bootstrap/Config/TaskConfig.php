<?php
/**
 *  di setting
 * User: Peter Wang
 * Date: 16/9/8
 * Time: 下午6:08
 */

namespace Trensy\Foundation\Bootstrap\Config;


class TaskConfig
{

    public static function getOptions()
    {
        return [
            "email" => \Trensy\Foundation\Bootstrap\Task\Email::class,
        ];
    }

}