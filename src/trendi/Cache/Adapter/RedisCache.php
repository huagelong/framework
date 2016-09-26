<?php
/**
 * User: Peter Wang
 * Date: 16/9/23
 * Time: 下午5:08
 */

namespace Trendi\Cache\Adapter;

use Trendi\Cache\CacheInterface;
use Trendi\Foundation\Storage\Redis;
use Trendi\Support\Serialization\Serialization;

class RedisCache implements CacheInterface
{

    public function get($key, $default = null)
    {
        $obj = new Redis();
        $result = $obj->get($key);
        if (!$result) return $default;
        if (function_exists("igbinary_serialize")) {
            $serializeObj = Serialization::get(3);
        } elseif (function_exists("msgpack_pack")) {
            $serializeObj = Serialization::get(2);
        } elseif (function_exists("hprose_serialize")) {
            $serializeObj = Serialization::get(5);
        } else {
            $serializeObj = Serialization::get(1);
        }
        return $serializeObj->xformat($result);
    }


    public function set($key, $value, $expire = -1)
    {
        $obj = new Redis();
        if (function_exists("igbinary_serialize")) {
            $serializeObj = Serialization::get(3);
        } elseif (function_exists("msgpack_pack")) {
            $serializeObj = Serialization::get(2);
        } elseif (function_exists("hprose_serialize")) {
            $serializeObj = Serialization::get(5);
        } else {
            $serializeObj = Serialization::get(1);
        }
        $data = $serializeObj->format($value);
        $result = $obj->set($key, $data);
        if ($expire > 0) $obj->expire($key, $expire);
        return $result;
    }

    public function del($key)
    {
        $obj = new Redis();
        return $obj->del($key);
    }

}