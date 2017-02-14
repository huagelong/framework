<?php
/**
 *  flash message
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

namespace Trensy\Support;


use Trensy\Foundation\Bootstrap\Session;

class Flash
{

    public static function get()
    {
        $data = self::session()->get(__CLASS__);
        self::session()->del(__CLASS__);
        return $data;
    }

    public static function set($type, $value)
    {
        $methods = [
            'info',
            'success',
            'warning',
            'danger',
            'error'
        ];
        if(in_array($type, $methods)){
            $data = [];
            $data['type'] = $type;
            $data['value'] = $value;
            self::session()->set(__CLASS__, $data);
        }
    }

    protected static function session()
    {
        return Session::getInstance();
    }


    public static function __callStatic($method, $args)
    {
        self::set($method,$args[0]);
    }

}