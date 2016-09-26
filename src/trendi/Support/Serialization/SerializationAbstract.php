<?php
/**
 * User: Peter Wang
 * Date: 16/9/19
 * Time: 下午6:44
 */

namespace Trendi\Support\Serialization;


abstract class SerializationAbstract
{
    public static $bodyOffset = 4;

    public abstract function format($data);

    public abstract function xformat($data);

    public static function setBodyOffset($bodyOffset)
    {
        self::$bodyOffset = $bodyOffset;
    }

    public static function getBodyOffset()
    {
        return self::$bodyOffset;
    }

    public function getSendContent($bufferData)
    {
        if (!$bufferData) return "";
        $len = strlen($bufferData);
        $packLen = pack("N", $len);
        return $packLen . $bufferData;
    }

    public function getBody($bufferData)
    {
        if (!$bufferData) return "";
        $content = substr($bufferData, self::$bodyOffset);
        return $content;
    }
}