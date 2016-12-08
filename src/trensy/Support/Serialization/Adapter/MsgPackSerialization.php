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

namespace Trensy\Support\Serialization\Adapter;

use Trensy\Support\Serialization\SerializationAbstract;

class MsgPackSerialization extends SerializationAbstract
{
    /**
     * 序列
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function format($data)
    {
        if (!function_exists("msgpack_pack")) {
            throw new \Exception("msgpack ext not found");
        }
        return $this->getSendContent(msgpack_pack($data));
    }

    /**
     * 反序列
     * @param $data
     * @return null
     * @throws \Exception
     */
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

    /**
     * 常规序列化
     * @param $data
     * @return mixed
     */
    public function trans($data)
    {
        return msgpack_pack($data);
    }

    /**
     * 常规反序列化
     * @param $data
     * @return mixed
     */
    public function xtrans($data)
    {
        return msgpack_unpack($data);
    }
    
}