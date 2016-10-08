<?php
/**
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 上午9:46
 */

namespace Trendi\Foundation\Bootstrap\Facade;


use Trendi\Support\Facade;

class Session extends Facade
{
    protected static function setFacadeAccessor()
    {
        return "session";
    }
}