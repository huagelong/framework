<?php
/**
 * User: Peter Wang
 * Date: 16/9/19
 * Time: 下午2:46
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
    public static function get($type)
    {
        $type = intval($type);
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
    }


}