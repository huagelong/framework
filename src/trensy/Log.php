<?php
/**
 *
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
use Trensy\Context;
use Trensy\Support\ElapsedTime;

class Log
{
    private static $callback = null;


    protected static function getClientIp()
    {
        $request =  Context::request();
        if(!$request) return "127.0.0.1";
        if ( $request->server->get(strtolower("HTTP_X_FORWARDED_FOR"))) { //#透过代理服务器取得客户端的真实 IP 地址
            $ip =  $request->server->get(strtolower("HTTP_X_FORWARDED_FOR"));
        } elseif ($request->server->get(strtolower("HTTP_CLIENT_IP"))) { //#客户端IP
            $ip = $request->server->get(strtolower("HTTP_CLIENT_IP"));
        } elseif ($request->server->get(strtolower("REMOTE_ADDR"))) { //#正在浏览当前页面用户的 IP 地址
            $ip =$request->server->get(strtolower("REMOTE_ADDR"));
        } elseif (getenv("HTTP_X_FORWARDED_FOR")) {  //#透过代理服务器取得客户端的真实 IP 地址
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("HTTP_CLIENT_IP")) {  //#客户端IP
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("REMOTE_ADDR")) {  //#正在浏览当前页面用户的 IP 地址
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "127.0.0.1";
        }
        return $ip;
    }

    protected static function preData()
    {
        $ip = static::getClientIp();

        $elapsedTime = ElapsedTime::runtime("sys_elapsed_time");

        $result = [];
        if(function_exists('posix_getpid')) $result[] = "pid:".posix_getpid();
        $result[] = "ip:".$ip."/".self::getOlineIp();
        $result[] = $elapsedTime."ms";

        return $result;
    }

    protected static function getOlineIp()
    {
        if(Context::hasSet("request")){
            $request = Context::request();
            $ip = $request->headers->get('x-forwarded-for');
            !$ip && $ip = $request->headers->get('x-real-ip', '127.0.0.1');

            is_array($ip) && $ip = $ip[0];

            $ip = explode(',', $ip);
            is_array($ip) && $ip = $ip[0];

            return $ip;
        }else{
            return "127.0.0.1";
        }
    }


    public static function register($callback)
    {
        self::$callback = $callback;
    }


    /**
     * 颜色初始化
     *
     * @param $foreground_colors
     * @param $background_colors
     */
    protected static function init()
    {
        // Set up shell colors
        $foreground_colors['black'] = '0;30';
        $foreground_colors['dark_gray'] = '1;30';
        $foreground_colors['blue'] = '0;34';
        $foreground_colors['light_blue'] = '1;34';
        $foreground_colors['green'] = '0;32';
        $foreground_colors['light_green'] = '1;32';
        $foreground_colors['cyan'] = '0;36';
        $foreground_colors['light_cyan'] = '1;36';
        $foreground_colors['red'] = '0;31';
        $foreground_colors['light_red'] = '1;31';
        $foreground_colors['purple'] = '0;35';
        $foreground_colors['light_purple'] = '1;35';
        $foreground_colors['brown'] = '0;33';
        $foreground_colors['yellow'] = '1;33';
        $foreground_colors['light_gray'] = '0;37';
        $foreground_colors['white'] = '1;37';
        return $foreground_colors;
    }

    protected static function outPut($type, $data)
    {

        if (self::$callback && (self::$callback instanceof \Closure)) {
            $closureParam = [$type,$data];
            call_user_func(self::$callback, $closureParam);
        }

        $msg = array_pop($data);
        if($type) array_unshift($data, $type);

        $data = array_slice($data, 0, 2);

        if(PHP_SAPI != 'cli'){
            echo $msg;
        }else{

            if($type == 'show'){
                $string = $msg;
            }else{
                $string = date('Y-m-d H:i:s')." [".implode("][",$data)."] ".$msg;
            }

            $foreground_colors = self::init();
            $color = [
                "info"=>"light_gray",
                "sysinfo"=>"dark_gray",
                "warn"=>"yellow",
                "debug"=>"green",
                "show"=>"green",
                "error"=>"red",
            ];
            if (isset($foreground_colors[$color[$type]])) {
                $colorStr = $foreground_colors[$color[$type]];
                $string = "\033[" . $colorStr . "m".$string;
            }
            $string = $string . "\033[0m\n";

            fwrite(STDOUT, $string);
        }
    }

    protected static function _outPut($name, $arguments)
    {
        $msg = $arguments;

        if(is_array($arguments)){
            $msg = print_r($arguments, true);
        }

        $data = self::preData();
        $data[]=$msg;
        self::outPut($name,$data);
    }

    public static function info($arguments){
        return self::_outPut("info", $arguments);
    }

    public static function sysinfo($arguments){
        return self::_outPut("sysinfo", $arguments);
    }

    public static function warn($arguments){
        return self::_outPut("warn", $arguments);
    }

    public static function debug($arguments){
        return self::_outPut("debug", $arguments);
    }

    public static function show($arguments){
        return self::_outPut("show", $arguments);
    }

    public static function error($arguments){
        return self::_outPut("error", $arguments);
    }


}