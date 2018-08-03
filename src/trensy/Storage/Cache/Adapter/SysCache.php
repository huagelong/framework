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

class SysCache implements CacheInterface
{

    protected static $cacheData = [];
    protected static $instance = null;

    public static function getInstance()
    {
        if(self::$instance) return self::$instance;
        self::$instance = new self();
        return self::$instance;
    }


    public function __construct()
    {

    }


    /**
     * 设置缓存
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function set($key, $value, $expire=0)
    {
        self::$cacheData[$key] = $value;

        return true;
    }

    /**
     * 获取缓存
     *
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return  isset(self::$cacheData[$key]) ? self::$cacheData[$key]:$default;
    }

    /**
     * 删除缓存
     *
     * @param $key
     * @return mixed
     */
    public function del($key)
    {
        unset(self::$cacheData[$key]);
        return true;
    }

    /**
     * 缓存是否存在
     *
     * @param $key
     * @return bool
     */
    public function exists($key)
    {
        return  isset(self::$cacheData[$key]) ? true:false;
    }

    /**
     * 清空全部
     *
     * @return bool
     */
    public function clearAll()
    {
        self::$cacheData=null;
        return true;
    }

}