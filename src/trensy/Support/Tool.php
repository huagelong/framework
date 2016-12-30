<?php
/**
 * User: Peter Wang
 * Date: 16/12/28
 * Time: 下午6:38
 */

namespace Trensy\Support;


class Tool
{

    public static function xCopy($source, $destination, $child = 1)
    {
        if (!is_dir($source)) {
            return false;
        }
        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }
        $handle = dir($source);
        while ($entry = $handle->read()) {
            if (($entry != ".") && ($entry != "..")) {
                if (is_dir($source . "/" . $entry)) {
                    if ($child) {
                        self::xCopy($source . "/" . $entry, $destination . "/" . $entry, $child);
                    }
                } else {
                    copy($source . "/" . $entry, $destination . "/" . $entry);
                }
            }
        }
        return true;
    }

    public static function encode($str, $key="abc")
    {
        $charset = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $urlhash = md5($key . $str);
        $len = strlen($urlhash);
        $urlhash_piece = substr($urlhash, $len / 4, $len / 4);
        //将分段的位与0x3fffffff做位与，0x3fffffff表示二进制数的30个1，即30位以后的加密串都归零
        //此处需要用到hexdec()将16进制字符串转为10进制数值型，否则运算会不正常
        $hex = hexdec($urlhash_piece) & 0x3fffffff;
        $encode = "";
        //生成6位
        for ($j = 0; $j < 6; $j++) {
            //将得到的值与0x0000003d,3d为61，即charset的坐标最大值
            $encode .= $charset[$hex & 0x0000003d];
            //循环完以后将hex右移5位
            $hex = $hex >> 5;
        }
        return $encode;
    }
}