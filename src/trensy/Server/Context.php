<?php
/**
 * 上下文
 *
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Server;

use Trensy\Support\Event;
class Context
{
    private static $map = [];

    public function __construct()
    {
        self::$map = [];
    }

    public function clear()
    {
        foreach (self::$map as $k => $v) {
            if (!$v[2]) {
                unset(self::$map[$k]);
            }
        }
    }

    public static function hasSet($key)
    {
        return isset(self::$map[$key][0]);
    }

    public function getAll()
    {
        return self::$map;
    }

    public function set($key, $value, $once = true, $keepLive = false)
    {
        if (isset(self::$map[$key]) && self::$map[$key][1] == true) {
            return;
        }
        self::$map[$key] = [$value, $once, $keepLive];
    }
    
    public function __call($method, $args)
    {
        if (!isset(self::$map[$method])) {
            return null;
        }
        return self::$map[$method][0];
    }

    public function __destruct()
    {
        Event::bind("clear", function () {
            self::$map = [];
        });
    }
}