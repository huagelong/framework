<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 2017/4/11
 * Time: 13:54
 */

namespace Trensy\Foundation;


trait Shortcut
{
    /**
     *  根据路由名称获取网址
     *
     * @param $routeName
     * @param array $params
     * @param string $groupName
     * @return string
     */
     public static function url($routeName, $params = [], $groupName='')
    {
        return \Trensy\Mvc\Route\RouteMatch::getInstance()->simpleUrl($routeName, $params, $groupName);
    }

    /**
     *  获取redis 对象
     *
     * @return \Trensy\Foundation\Storage\Redis
     */
     public static function redis()
    {
        return new \Trensy\Foundation\Storage\Redis();
    }

    /**
     *  config 对象
     *
     * @return \Trensy\Config\Config
     */
     public static function config()
    {
        return new \Trensy\Config\Config();
    }

    /**
     *  session 对象
     *
     * @return \Trensy\Http\Session
     */
     public static function session()
    {
        return \Trensy\Foundation\Bootstrap\Session::getInstance();
    }

    /**
     * 缓存对象
     * @return \Trensy\Storage\Cache\Adapter\RedisCache;
     */
     public static function cache()
    {
        return new \Trensy\Storage\Cache\Adapter\RedisCache();
    }

    /**
     * 缓存对象
     * @return \Trensy\Storage\Cache\Adapter\ApcCache;
     */
     public static function syscache()
    {
        return new \Trensy\Storage\Cache\Adapter\ApcCache();
    }

    /**
     * 输出
     * @return string;
     */
     public static function dump($str, $isReturn=false)
    {
        if(!$isReturn){
            $data = debug_backtrace(2, 2);
            $line = isset($data[0])?$data[0]:null;
            $func = isset($data[1])?$data[1]:null;
            if($func){
                \Trensy\Support\Log::show("{$func['function']}(): {$line['file']} . (line:{$line['line']})");
            }
            else{
                \Trensy\Support\Log::show(" {$line['file']} . (line:{$line['line']})");
            }
            return \Trensy\Support\Log::show($str);
        }
        ob_start();
        \Trensy\Support\Log::show($str);
        $msg = ob_get_clean();
        return $msg;
    }

    /**
     * 输出
     * @return string;
     */
     public static function debug($str, $isReturn=false)
    {
        if(!$isReturn){
            return \Trensy\Support\Log::debug($str);
        }
        ob_start();
        \Trensy\Support\Log::debug($str);
        $msg = ob_get_clean();
        return $msg;
    }

     public static function backtrace()
    {
        $data = debug_backtrace(2, 7);
        if($data){
            $data = array_splice($data, 2);
            \Trensy\Support\Log::show('{');
            foreach ($data as $v){
                $str = implode(" ", $v);
                \Trensy\Support\Log::show($str);
            }
            \Trensy\Support\Log::show('}');
        }

    }

    /**
     * 404错误
     */
     public static function page404($str='')
    {
        throw new \Trensy\Support\Exception\Page404Exception($str);
    }

    /**
     * 断点
     */
     public static function throwExit($str=null)
    {
        if(!$str){
            list($line, $func) = debug_backtrace(2, 2);
            \Trensy\Support\Log::show("{$func['function']}(): {$line['file']} . (line:{$line['line']})");
        }
        $str && self::dump($str);
        throw new \Trensy\Support\Exception\RuntimeExitException("exit");
    }

    /**
     * 多语言
     */
     public static function l($str, $params=[])
    {
        return \Trensy\Support\Lang::get($str, $params);
    }

    /**
     * isset
     */
     public static function array_isset($arr, $key, $default=null)
    {
        return isset($arr[$key]) ? $arr[$key]:$default;
    }

    /**
     * isset
     */
     public static function trans($arr)
    {
        return  \Trensy\Support\Serialization\Serialization::get()->trans($arr);
    }

    /**
     * isset
     */
     public static function xtrans($arr)
    {
        return  \Trensy\Support\Serialization\Serialization::get()->xtrans($arr);
    }

    /**
     * 输出后清除变量
     */
     public static function responseEnd($callback)
    {
        \Trensy\Support\Event::bind("request.end",$callback);
    }

    /**
     * 非阻塞程序处理
     */
     public static function nonBlock($callback,$interval=1)
    {
        \Trensy\Support\Timer::after($interval,$callback);
    }

    /**
     *  依赖注入
     *
     * @param $str
     * @return object
     * @throws \Trensy\Di\Exception\DiNotDefinedException
     */
     public static function di($str)
    {
        return \Trensy\Di\Di::get($str);
    }
}