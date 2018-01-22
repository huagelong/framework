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

namespace Trensy\Support;

use Trensy\Support\Log;
class Tool
{

    public static function xCopy($source, $destination, $child = 1, $except=[])
    {
        if (!is_dir($source)) {
            return false;
        }
        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }
        $handle = dir($source);
        $source = Dir::formatPath($source);
        while ($entry = $handle->read()) {
            if (($entry != ".") && ($entry != "..")) {
                if (is_dir($source . $entry)) {
                    if ($child) {
                        if($except && in_array($source . $entry, $except)){
                            continue;
                        }
                        self::xCopy($source . $entry, $destination . "/" . $entry, $child, $except);
                    }
                } else {
                    copy($source . $entry, $destination . "/" . $entry);
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

    /**
     * 自定义jsoncode
     * @param $json
     * @return mixed
     */
    public static function myJsonEncode($json)
    {
        array_walk_recursive($json, function (&$value, $key)
        {
            if(is_string($value) && is_numeric($value))
            {
                // check if value doesn't starts with 0 or +
                if(!preg_match('/^(\+|0)/', $value))
                {
                    // cast $value to int or float
                    $value   += 0;
                }
            }
        });
        //JSON_NUMERIC_CHECK
        return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取随机数
     * @return string
     */
    public static function guuid(){
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);
        return $uuid;
    }

    /**
     * 去除代码中的空白和注释
     * @param string $content 代码内容
     * @return string
     */
    public static function strip_whitespace($content) {
        $stripStr   = '';
        //分析php源码
        $tokens     = token_get_all($content);
        $last_space = false;
        for ($i = 0, $j = count($tokens); $i < $j; $i++) {
            if (is_string($tokens[$i])) {
                $last_space = false;
                $stripStr  .= $tokens[$i];
            } else {
                switch ($tokens[$i][0]) {
                    //过滤各种PHP注释
                    case T_COMMENT:
                    case T_DOC_COMMENT:
                        break;
                    //过滤空格
                    case T_WHITESPACE:
                        if (!$last_space) {
                            $stripStr  .= ' ';
                            $last_space = true;
                        }
                        break;
                    case T_START_HEREDOC:
                        $stripStr .= "<<<THINK\n";
                        break;
                    case T_END_HEREDOC:
                        $stripStr .= "THINK;\n";
                        for($k = $i+1; $k < $j; $k++) {
                            if(is_string($tokens[$k]) && $tokens[$k] == ';') {
                                $i = $k;
                                break;
                            } else if($tokens[$k][0] == T_CLOSE_TAG) {
                                break;
                            }
                        }
                        break;
                    default:
                        $last_space = false;
                        $stripStr  .= $tokens[$i][1];
                }
            }
        }
        return $stripStr;
    }

    /**
     * fix Uncaught ErrorException: cli_set_process_title(): cli_set_process_title had an error: Not initialized correctly
     * @param $name
     */
    public static function swoole_set_process_name($name){
        try{
//            PHP_OS
            if (function_exists('cli_set_process_title')) {
                cli_set_process_title($name);
            } elseif (function_exists('setproctitle')) {
                setproctitle($name);
            } else{
                Log::error('Install the proctitle PECL to be able to change the process title.');
            }
        }catch (\Exception $e){

            Log::warn($e->getMessage());
        }
    }

}
