<?php
/**
 * User: Peter Wang
 * Date: 16/9/9
 * Time: 下午12:18
 */
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

if (!function_exists('url')) {
    function url($routeName, $params = [])
    {
        return \Trendi\Mvc\Route\RouteMatch::getInstance()->url($routeName, $params);
    }
}

if (!function_exists('pp')) {
    function pp($var)
    {
        foreach (func_get_args() as $var) {
            $cloner = new VarCloner();
            $dumper = new HtmlDumper();
            $handler = function ($var) use ($cloner, $dumper) {
                $dumper->dump($cloner->cloneVar($var));
            };
            return call_user_func($handler, $var);
        }
    }
}

if (!function_exists('redis')) {
    function redis()
    {
        return new \Trendi\Foundation\Storage\Redis();
    }
}

if (!function_exists('config')) {
    /**
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
     * @return \Trendi\Cache\Adapter\RedisCache;
     */
    function cache()
    {
        return new \Trendi\Cache\Adapter\RedisCache();
    }
}