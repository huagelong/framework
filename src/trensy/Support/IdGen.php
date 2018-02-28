<?php
/**
 *  ID生成
 *
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


class IdGen
{
    const EPOCH = 1479533469598;
    const max12bit = 4095;
    const max41bit = 1099511627775;


    public static function get($mId) {
        $time = floor(microtime(true) * 1000);
        $time -= self::EPOCH;
        $base = decbin(self::max41bit + $time);

        $machineid = str_pad(decbin($mId), 10, "0", STR_PAD_LEFT);
        $random = str_pad(decbin(mt_rand(0, self::max12bit)), 12, "0", STR_PAD_LEFT);

        $base = $base.$machineid.$random;

        return bindec($base);
    }

    public static function timeFromId($snowflakeId) {
        return bindec(substr(decbin($snowflakeId),0,41)) - self::max41bit + self::EPOCH;
    }
}