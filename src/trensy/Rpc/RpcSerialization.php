<?php
/**
 *  数据序列化处理以及route 匹配
 *
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 下午7:31
 */

namespace Trensy\Rpc;

use Trensy\Support\Serialization\Serialization;

class RpcSerialization
{

    private $serializeObj = null;

    public function __construct($serializeType = 1, $bodyOffset = 4)
    {
        $this->serializeObj = Serialization::get($serializeType);
        $this->serializeObj->setBodyOffset($bodyOffset);
    }

    public function xformat($data)
    {
        return $this->serializeObj->xformat($data);
    }

    public function format($data)
    {
        $this->serializeObj->format($data);
    }
}