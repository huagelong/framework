<?php
/**
 *  函数
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */

if(!function_exists("env")){
    function env($str, $default=null)
    {
        $getEnv = getenv($str);
        return $getEnv?$getEnv:$default;
    }
}

if(!function_exists("debug")){
    function debug($str)
    {
        \Trensy\Log::debug($str);
        return true;
    }
}