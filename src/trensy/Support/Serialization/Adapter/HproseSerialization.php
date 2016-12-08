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

class HproseSerialization extends SerializationAbstract
{
    /**
     * 序列
     * @param $data
     * @return string
     * @throws \Exception
     */
    public function format($data)
    {
        if (!function_exists("hprose_serialize")) {
            throw new \Exception("hprose ext not found");
        }
        return $this->getSendContent(hprose_serialize($data));
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

        if (!function_exists("hprose_unserialize")) {
            throw new \Exception("hprose ext not found");
        }

        $result = hprose_unserialize($body);
        return $result;
    }

    /**
     * 常规序列化
     * @param $data
     * @return mixed
     */
    public function trans($data)
    {
        return hprose_serialize($data);
    }

    /**
     * 常规反序列化
     * @param $data
     * @return mixed
     */
    public function xtrans($data)
    {
        return hprose_unserialize($data);
    }
}