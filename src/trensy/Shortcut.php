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

namespace Trensy;


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
     * @return \Trensy\Config
     */
     public static function config()
    {
        return \Trensy\Config::getInstance();
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
     *  session 对象
     *
     * @return string
     */
    public static function getCookie($key, $default=null)
    {
        return self::request()->cookies->get($key, $default);
    }

    /**
     * 缓存对象
     * @return \Trensy\Storage\Cache\Adapter\RedisCache;
     */
     public static function cache()
    {
        $name = self::config()->get('app.app_name');
        return new \Trensy\Storage\Cache\Adapter\RedisCache($name);
    }

    public static function fileCache()
    {
        $name = self::config()->get('app.app_name');
        $cacheDir = STORAGE_PATH."/file_cache";
        return new \Trensy\Storage\Cache\Adapter\FileCache($name, $cacheDir);
    }

    /**
     * 缓存对象
     * @return \Trensy\Storage\Cache\Adapter\SysCache;
     */
     public static function syscache()
    {
        return \Trensy\Storage\Cache\Adapter\SysCache::getInstance();
    }


    /**
     * 缓存对象
     * @return \Trensy\Storage\Cache\Adapter\ApcCache;
     */
    public static function apccache()
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
                \Trensy\Log::show("{$func['function']}(): {$line['file']} . (line:{$line['line']})");
            }
            else{
                \Trensy\Log::show(" {$line['file']} . (line:{$line['line']})");
            }
            return \Trensy\Log::show($str);
        }
        ob_start();
        \Trensy\Log::show($str);
        $msg = ob_get_clean();
        return $msg;
    }

    /**
     * 输出
     * @return string;
     */
     public static function debug($str, $isReturn=false, $line=0)
    {
        if(!$isReturn){
            $data = debug_backtrace(2, 7);
            $result = isset($data[$line])?$data[$line]:$data[0];
            $func = isset($result['function'])?$result['function']:null;
            $file = isset($result['file'])?$result['file']:null;
            $strTmp = "";
            if($file){
                if($func){
                    $strTmp = "{$result['function']}(): {$result['file']} . (line:{$result['line']})";
                }
                else{
                    $strTmp = " {$result['file']} . (line:{$result['line']})";
                }
            }

            if($strTmp) \Trensy\Log::show($strTmp);

            return \Trensy\Log::debug($str);
        }
        ob_start();
        \Trensy\Log::debug($str);
        $msg = ob_get_clean();
        return $msg;
    }

     public static function backtrace()
    {
        $data = debug_backtrace(2, 7);
        if($data){
            $data = array_splice($data, 2);
            \Trensy\Log::show('{');
            foreach ($data as $v){
                $str = implode(" ", $v);
                \Trensy\Log::show($str);
            }
            \Trensy\Log::show('}');
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
            \Trensy\Log::show("{$func['function']}(): {$line['file']} . (line:{$line['line']})");
        }
        $str && self::dump($str);
        throw new \Trensy\Support\Exception\RuntimeExitException("exit");
    }

    /**
     * 多语言
     */
     public static function l($str, $params=[])
    {
        return \Trensy\Lang::get($str, $params);
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
        return  \Trensy\Support\Serialization::get()->trans($arr);
    }

    /**
     * isset
     */
     public static function xtrans($arr)
    {
        return  \Trensy\Support\Serialization::get()->xtrans($arr);
    }

    /**
     * 输出后清除变量
     */
     public static function responseEnd($callback)
    {
        \Trensy\Event::bind("request.end",$callback);
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
        return \Trensy\Di::get($str);
    }

    /**
     *  @return \Trensy\Http\Request
     */
    public static function request()
    {
        return \Trensy\Context::request();
    }

    /**
     * @return \Trensy\Http\response
     */
    public static function response()
    {
        return \Trensy\Context::response();
    }
}