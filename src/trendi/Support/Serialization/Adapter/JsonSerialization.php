<?php
/**
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 下午7:34
 */

namespace Trendi\Support\Serialization\Adapter;

use Trendi\Support\Serialization\SerializationAbstract;

class JsonSerialization extends SerializationAbstract
{
    public function format($data)
    {
        return $this->getSendContent(json_encode($data));
    }

    public function xformat($data)
    {
        $body = $this->getBody($data);
        if (!$body) {
            return null;
        }

        $result = json_decode($body, true);
        return $result;
    }
}