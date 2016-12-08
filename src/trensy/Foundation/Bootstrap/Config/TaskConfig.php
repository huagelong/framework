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


class TaskConfig
{

    public static function getOptions()
    {
        return [
            "email" => \Trensy\Foundation\Bootstrap\Task\Email::class,
        ];
    }

}