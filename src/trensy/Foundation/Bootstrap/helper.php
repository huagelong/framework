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
        return \Trensy\Mvc\Route\RouteMatch::getInstance()->url($routeName, $params);
    }
}

if (!function_exists('redis')) {
    /**
     *  获取redis 对象
     *
     * @return \Trensy\Foundation\Storage\Redis
     */
    function redis()
    {
        return new \Trensy\Foundation\Storage\Redis();
    }
}

if (!function_exists('config')) {
    /**
     *  config 对象
     *
     * @return mixed
     */
    function config()
    {
        return new \Trensy\Config\Config();
    }
}

if (!function_exists('session')) {
    /**
     *  session 对象
     *
     * @return mixed
     */
    function session()
    {
        return \Trensy\Foundation\Bootstrap\Session::getInstance();
    }
}

if (!function_exists('cache')) {
    /**
     * 缓存对象
     * @return \Trensy\Cache\Adapter\RedisCache;
     */
    function cache()
    {
        return new \Trensy\Cache\Adapter\RedisCache();
    }
}

if (!function_exists('syscache')) {
    /**
     * 缓存对象
     * @return \Trensy\Cache\Adapter\ApcCache;
     */
    function syscache()
    {
        return new \Trensy\Cache\Adapter\ApcCache();
    }
}


if (!function_exists('dump')) {
    /**
     * 输出
     * @return string;
     */
    function dump($str, $isReturn=false)
    {
        if(!$isReturn){
            return \Trensy\Support\Log::show($str);
        }
        ob_start();
        \Trensy\Support\Log::show($str);
        $msg = ob_get_clean();
        return $msg;
    }
}

if (!function_exists('debug')) {
    /**
     * 输出
     * @return string;
     */
    function debug($str, $isReturn=false)
    {
        if(!$isReturn){
            return \Trensy\Support\Log::debug($str);
        }
        ob_start();
        \Trensy\Support\Log::debug($str);
        $msg = ob_get_clean();
        return $msg;
    }
}

if (!function_exists('page404')) {
    /**
     * 404错误
     */
    function page404($str='')
    {
        throw new \Trensy\Support\Exception\Page404Exception($str);
    }
}

if (!function_exists('throwExit')) {
    /**
     * 断点
     */
    function throwExit($str=null)
    {
        $str && dump($str);
        throw new \Trensy\Support\Exception\RuntimeExitException("exit");
    }
}

if (!function_exists('l')) {
    /**
     * 多语言
     */
    function l($str, $params=[])
    {
        return \Trensy\Support\Lang::get($str, $params);
    }
}

