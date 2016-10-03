<?php
/**
 * User: Peter Wang
 * Date: 16/9/9
 * Time: 下午12:18
 */

if (!function_exists('url')) {
    /**
     *  根据路由名称获取网址
     *
     * @param $routeName
     * @param array $params
     * @return string
     */
    function url($routeName, $params = [])
    {
        return \Trendi\Mvc\Route\RouteMatch::getInstance()->url($routeName, $params);
    }
}

if (!function_exists('redis')) {
    /**
     *  获取redis 对象
     *
     * @return \Trendi\Foundation\Storage\Redis
     */
    function redis()
    {
        return new \Trendi\Foundation\Storage\Redis();
    }
}

if (!function_exists('config')) {
    /**
     *  config 对象
     *
     * @param $str
     * @param null $default
     * @return array
     */
    function config($str, $default = null)
    {
        return \Trendi\Config\Config::get($str, $default);
    }
}

if (!function_exists('cache')) {
    /**
     * 缓存对象
     * @return \Trendi\Cache\Adapter\RedisCache;
     */
    function cache()
    {
        return new \Trendi\Cache\Adapter\RedisCache();
    }
}

if (!function_exists('debug')) {
    /**
     * 缓存对象
     * @return \Trendi\Support\Log;
     */
    function debug($str)
    {
        $str = print_r($str, true);
        return \Trendi\Support\Log::debug($str);
    }
}
