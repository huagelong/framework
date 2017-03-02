<?php
/**
 *  job处理
 *
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Monitor;

use Trensy\Support\Serialization\Serialization;

class MonitorSerialization
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
        return $this->serializeObj->format($data);
    }
}