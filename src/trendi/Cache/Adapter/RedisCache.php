<?php
/**
 * User: Peter Wang
 * Date: 16/9/23
 * Time: 下午5:08
 */

namespace Trendi\Cache\Adapter;

use Trendi\Cache\CacheInterface;
use Trendi\Foundation\Storage\Redis;

class RedisCache implements CacheInterface
{

    public function get($key, $default = null)
    {
        $obj = new Redis();
        $result = $obj->get($key);
        if (!$result) return $default;
        return $result;
    }


    public function set($key, $value, $expire = -1)
    {
        $obj = new Redis();
        if ($expire > 0) {
            $result = $obj->setex($key, $expire, $value);
        }else{
            $result = $obj->set($key, $value);
        }
        return $result;
    }

    public function del($key)
    {
        $obj = new Redis();
        return $obj->del($key);
    }

}