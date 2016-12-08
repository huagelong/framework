<?php
/**
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Support\Serialization;


abstract class SerializationAbstract
{
    public static $bodyOffset = 4;

    public abstract function format($data);

    public abstract function xformat($data);
    
    public abstract function trans($data);
    
    public abstract function xtrans($data);

    public static function setBodyOffset($bodyOffset)
    {
        self::$bodyOffset = $bodyOffset;
    }

    public static function getBodyOffset()
    {
        return self::$bodyOffset;
    }

    /**
     * 数据流发送字符串衔接
     *
     * @param $bufferData
     * @return string
     */
    public function getSendContent($bufferData)
    {
        if (!$bufferData) return "";
        $len = strlen($bufferData);
        $packLen = pack("N", $len);
        return $packLen . $bufferData;
    }

    /**
     * 数据流body内容获取
     *
     * @param $bufferData
     * @return string
     */
    public function getBody($bufferData)
    {
        if (!$bufferData) return "";
        $content = substr($bufferData, self::$bodyOffset);
        return $content;
    }
}