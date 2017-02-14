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
    const RUN_MODE_TEST = "test";
    const RUN_MODE_ONLINE = "online";

    private static $runMode = null;
    private static $env = null;


    /**
     * 获取执行模式,影响调试
     * @return int
     */
    public static function getRunMode()
    {
        return self::$runMode?self::$runMode:self::RUN_MODE_ONLINE;
    }

    /**
     * 获取执行环境,影响config
     * @return string
     */
    public static function getEnv()
    {
        return self::$env?self::$env:self::RUN_MODE_ONLINE;
    }

    /**
     *  初始化
     * @return string
     * @throws \EnvInvalidException
     */
    public static function init()
    {
        if (self::$runMode) return self::$runMode;
        $env = getenv("TRENSY_RUNMODE");//test.dev
        if (!$env) {
            $env = get_cfg_var("TRENSY_RUNMODE");
        }

        if(defined("TRENSY_RUNMODE")){
            $env = TRENSY_RUNMODE;
        }

        if ($env) {
            $envArr = explode(".", $env);

            if (count($envArr) < 2) {
                throw new \EnvInvalidException(" 环境设置错误, 需要用test.developer 类似设置~");
            }

            if (!in_array($envArr[0], [self::RUN_MODE_TEST, self::RUN_MODE_ONLINE])) {
                throw new \EnvInvalidException(" 运行模式只能是 " . self::RUN_MODE_TEST . "," . self::RUN_MODE_ONLINE . "~");
            }

            self::$runMode = $envArr[0];
            self::$env = $envArr[1];
        }

        !self::$runMode && self::$runMode= self::RUN_MODE_ONLINE;
        !self::$env && self::$env= self::RUN_MODE_ONLINE;
    }
}