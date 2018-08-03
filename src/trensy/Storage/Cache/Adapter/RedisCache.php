<?php
/**
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */
namespace Trensy\Storage\Cache\Adapter;

use Trensy\Storage\Cache\CacheInterface;
use Trensy\Foundation\Storage\Redis;
use Trensy\Support\Serialization;

class RedisCache implements CacheInterface
{

    protected $redisKey = "";

    public function __construct($name='trensy')
    {
        $this->redisKey = $name."_redis_cache_key";
    }

    protected function parseKey($key)
    {
        return strlen($key)>50 ? substr($key,0,50)."_".md5($key):$key;
    }

    /**
     * 获取缓存
     * 
     * @param $key
     * @param null $default
     * @return null
     */
    public function get($key, $default = null)
    {
        $key = $this->parseKey($key);

        $obj = new Redis();
        $result = $obj->get($key);
        if (!$result) return $default;
        $result = Serialization::get()->xtrans($result);
        return $result;
    }


    /**
     * 设置缓存
     * @param $key
     * @param $value
     * @param int $expire  过期时间 单位s
     * @return mixed
     */
    public function set($key, $value, $expire = -1)
    {
        $key = $this->parseKey($key);

        $obj = new Redis();
        
        $value = Serialization::get()->trans($value);


        $obj->sadd($this->redisKey, [$key]);

        if ($expire > 0) {
            $result = $obj->setex($key, $expire, $value);
        } else {
            $result = $obj->set($key, $value);
        }
        return $result;
    }

    /**
     * 删除key
     * 
     * @param $key
     * @return mixed
     */
    public function del($key)
    {
        $key = $this->parseKey($key);

        $obj = new Redis();
        return $obj->del($key);
    }

    public function clearAll()
    {

        $obj = new Redis();
        while($key = $obj->spop($this->redisKey))
        {
            if($key){
                $obj->del($key);
            }
        }
    }

    public function getFullCacheKey()
    {
        return $this->redisKey;
    }


    public function getCacheCount()
    {
        $obj = new Redis();
        $len =$obj->scard($this->redisKey);
        return $len;
    }

    public function keylist($page=1, $pageSize=20)
    {
        $obj = new Redis();

        $len =$obj->scard($this->redisKey);
        $len = $len-1;

        $start = ($page-1)*$pageSize;
        $start = $start>$len?$len:$start;

        $all = $obj->smembers($this->redisKey);
        if(!$all) return [];
        $ret = array_slice($all, $start, $pageSize);
        return $ret;
    }

}