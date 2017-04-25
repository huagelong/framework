<?php
/**
 * runmode 环境切换
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Support;


class RunMode
{
    const RUN_MODE_ONLINE = "online";

    private static $runMode = null;


    /**
     * 获取执行模式,影响调试
     * @return int
     */
    public static function getRunMode()
    {
        return self::$runMode?self::$runMode:self::RUN_MODE_ONLINE;
    }

    /**
     *  初始化
     * @return string
     * @throws \EnvInvalidException
     */
    public static function init()
    {
        if (self::$runMode) return self::$runMode;
        $env = getenv("TRENSY_RUNMODE");
        if (!$env) {
            $env = get_cfg_var("TRENSY_RUNMODE");
        }

        if(defined("TRENSY_RUNMODE")){
            $env = TRENSY_RUNMODE;
        }

        if ($env) {
            self::$runMode = $env;
        }

        !self::$runMode && self::$runMode= self::RUN_MODE_ONLINE;
    }
}