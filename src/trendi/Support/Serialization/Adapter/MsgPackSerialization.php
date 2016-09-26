<?php
/**
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 下午7:34
 */

namespace Trendi\Support\Serialization\Adapter;

use Trendi\Support\Serialization\SerializationAbstract;

class MsgPackSerialization extends SerializationAbstract
{
    public function format($data)
    {
        if (!function_exists("msgpack_pack")) {
            throw new \Exception("msgpack ext not found");
        }
        return $this->getSendContent(msgpack_pack($data));
    }

    public function xformat($data)
    {
        $body = $this->getBody($data);
        if (!$body) {
            return null;
        }

        if (!function_exists("msgpack_unpack")) {
            throw new \Exception("msgpack ext not found");
        }

        $result = msgpack_unpack($body);
        return $result;
    }
}