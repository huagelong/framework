<?php
/**
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 下午7:34
 */

namespace Trendi\Support\Serialization\Adapter;

use Trendi\Support\Serialization\SerializationAbstract;

class HproseSerialization extends SerializationAbstract
{
    public function format($data)
    {
        if (!function_exists("hprose_serialize")) {
            throw new \Exception("hprose ext not found");
        }
        return $this->getSendContent(hprose_serialize($data));
    }

    public function xformat($data)
    {
        $body = $this->getBody($data);
        if (!$body) {
            return null;
        }

        if (!function_exists("hprose_unserialize")) {
            throw new \Exception("hprose ext not found");
        }

        $result = hprose_unserialize($body);
        return $result;
    }
}