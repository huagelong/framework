<?php
/**
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Foundation\Bootstrap\Facade;


use Trensy\Support\Facade;

class Log extends Facade
{
    protected static function setFacadeAccessor()
    {
        return "log";
    }
}