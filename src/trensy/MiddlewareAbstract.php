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

namespace Trensy;

use Trensy\Foundation\AnnotationLoadInterface;

abstract class MiddlewareAbstract implements AnnotationLoadInterface
{

    abstract public function perform();

    /**
     * @return \Trensy\Http\Request
     */
    public function getRequest()
    {
        return Context::request();
    }

    /**
     * @return \Trensy\Http\Response
     */
    public function getResponse()
    {
        return Context::response();
    }

}