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

use Trensy\Support\Serialization\Adapter\DefaultSerialization;
use Trensy\Support\Serialization\Adapter\HproseSerialization;
use Trensy\Support\Serialization\Adapter\IgbinarySerialization;
use Trensy\Support\Serialization\Adapter\JsonSerialization;
use Trensy\Support\Serialization\Adapter\MsgPackSerialization;

class Serialization
{

    /**
     * 获取序列方案
     * 
     * @param $type
     * @return DefaultSerialization|HproseSerialization|IgbinarySerialization|JsonSerialization|MsgPackSerialization
     */
    public static function get($type=0)
    {
        $type = intval($type);
        if($type){
            switch ($type) {
                case 1:
                    return new DefaultSerialization();
                case 2:
                    return new MsgPackSerialization();
                case 3:
                    return new IgbinarySerialization();
                case 4:
                    return new JsonSerialization();
                case 5:
                    return new HproseSerialization();
                default:
                    return new DefaultSerialization();
            }
        }else{
            return new DefaultSerialization();
        }
    }


}