<?php
/**
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */

namespace Trensy\Mvc;


class AssignData
{
    protected $assignData = [];

    public function getAssignData()
    {
        return $this->assignData;
    }

    public function __set($name, $value)
    {
        $this->assignData[$name] = $value;
    }
    
    public function __get($name)
    {
        return isset($this->assignData[$name])?$this->assignData[$name]:null;
    }
}