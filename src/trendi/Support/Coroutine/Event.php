<?php
/**
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 上午9:13
 */

namespace Trendi\Support\Coroutine;


class Event
{
    private static $evtMap = [];

    const NORMAL_EVENT = 1;
    const ONCE_EVENT = 2;

    public static function clear()
    {
        self::$evtMap = [];
    }

    public static function register($evtName)
    {
        if (!isset(self::$evtMap[$evtName])) {
            self::$evtMap[$evtName] = [];
        }
    }

    public static function unregister($evtName)
    {
        if (isset(self::$evtMap[$evtName])) {
            unset(self::$evtMap[$evtName]);
        }
    }

    public static function once($evtName, $callback)
    {
        self::register($evtName);

        self::$evtMap[$evtName][] = [
            'callback' => $callback,
            'evtType' => Event::ONCE_EVENT,
        ];
    }

    public static function bind($evtName, $callback)
    {
        self::register($evtName);

        self::$evtMap[$evtName][] = [
            'callback' => $callback,
            'evtType' => Event::NORMAL_EVENT,
        ];
    }

    public static function unbind($evtName, $callback)
    {
        if (!isset(self::$evtMap[$evtName]) || !self::$evtMap[$evtName]) {
            return false;
        }

        foreach (self::$evtMap[$evtName] as $key => $evt) {
            $cb = $evt['callback'];
            if ($cb == $callback) {
                unset(self::$evtMap[$evtName][$key]);
                return true;
            }
        }
        return false;
    }

    public static function fire($evtName, $args = null, $loop = true)
    {
        if (isset(self::$evtMap[$evtName]) && self::$evtMap[$evtName]) {
            self::fireEvents($evtName, $args, $loop);
        }
    }

    private static function fireEvents($evtName, $args = null, $loop = true)
    {
        $result = [];
        foreach (self::$evtMap[$evtName] as $key => $evt) {
            $callback = $evt['callback'];
            $evtType = $evt['evtType'];
            $result[] = call_user_func($callback, $args);

            if (Event::ONCE_EVENT === $evtType) {
                unset(self::$evtMap[$evtName][$key]);
            }

            if (false === $loop) {
                break;
            }
        }
        return $result;
    }
}