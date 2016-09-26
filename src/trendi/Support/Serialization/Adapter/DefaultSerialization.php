<?php
/**
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 下午7:34
 */

namespace Trendi\Support\Serialization\Adapter;

use Trendi\Support\Serialization\SerializationAbstract;

class DefaultSerialization extends SerializationAbstract
{

    public function format($data)
    {
        $resultData = serialize($data);

        return $this->getSendContent($resultData);
    }

    public function xformat($data)
    {
        $body = $this->getBody($data);
        if (!$body) {
            return null;
        }
        $result = unserialize($body);
        return $result;
    }
}