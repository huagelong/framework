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

namespace Trensy\Server\Facade;


use Trensy\Support\Facade;

class Task extends Facade
{
    protected static function setFacadeAccessor()
    {
        return "task";
    }
}