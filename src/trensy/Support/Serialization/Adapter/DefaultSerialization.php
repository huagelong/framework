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

class DefaultSerialization extends SerializationAbstract
{

    /**
     * 序列
     * @param $data
     * @return string
     */
    public function format($data)
    {
        $resultData = serialize($data);

        return $this->getSendContent($resultData);
    }

    /**
     * 反序列
     * @param $data
     * @return null
     */
    public function xformat($data)
    {
        $body = $this->getBody($data);
        if (!$body) {
            return null;
        }
        $result = unserialize($body);
        return $result;
    }

    /**
     * 常规序列化
     * @param $data
     * @return mixed
     */
    public function trans($data)
    {
        return serialize($data);
    }

    /**
     * 常规反序列化
     * @param $data
     * @return mixed
     */
    public function xtrans($data)
    {
        return unserialize($data);
    }
}