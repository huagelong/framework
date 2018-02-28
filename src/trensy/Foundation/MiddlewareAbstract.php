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

namespace Trensy\Foundation;

abstract class MiddlewareAbstract implements DocLoadInterface
{
    public $params;

    abstract public function perform();

    public function getParams($key=null){
        if($key===null) return $this->params;
        return isset($this->params[$key])?$this->params[$key]:null;
    }

    /**
     * @return \Trensy\Http\Request
     */
    public function getRequest()
    {
        return $this->getParams(0);
    }

    /**
     * @return \Trensy\Http\Response
     */
    public function getResponse()
    {
        return $this->getParams(1);
    }

}