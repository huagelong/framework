<?php
/**
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 下午7:34
 */

namespace Trendi\Support\Serialization\Adapter;

use Trendi\Support\Serialization\SerializationAbstract;

class IgbinarySerialization extends SerializationAbstract
{
    public function format($data)
    {
        if (!function_exists("igbinary_serialize")) {
            throw new \Exception("igbinary ext not found");
        }
        return $this->getSendContent(igbinary_serialize($data));
    }

    public function xformat($data)
    {
        $body = $this->getBody($data);
        if (!$body) {
            return null;
        }

        if (!function_exists("igbinary_unserialize")) {
            throw new \Exception("msgpack ext not found");
        }

        $result = igbinary_unserialize($body);
        return $result;
    }
}