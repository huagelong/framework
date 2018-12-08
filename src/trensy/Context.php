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
 * @version         3.0.0
 */

namespace Trensy;

use Trensy\Event;

class Context
{
    private static $map = [];

    public static function clear()
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

    public static function getAll()
    {
        return self::$map;
    }

    public static function set($key, $value, $once = false, $keepLive = false)
    {
        if (isset(self::$map[$key]) && self::$map[$key][1] == true) {
            return;
        }
        self::$map[$key] = [$value, $once, $keepLive];
    }

    public static function get($key)
    {
        if (!isset(self::$map[$key])) {
            return null;
        }

        $value = self::$map[$key][0];
        if(self::$map[$key][1]){
            unset(self::$map[$key]);
        }
        return $value;
    }
    
    public static function __callstatic($method, $args)
    {
        return self::get($method);
    }
}