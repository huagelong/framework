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

use Trensy\Context;
use Trensy\Controller;
use Trensy\Foundation\AnnotationLoadInterface;

abstract class WidgetAbstract implements AnnotationLoadInterface
{

    public function render($path, $data=[])
    {
        $controller = new Controller();
        return $controller->render($path, $data);
    }

    /**
     * @return \Trensy\Http\Request;
     */
    public function getRequest()
    {
        $request = Context::request();
        return $request;
    }

    /**
     * @return \Trensy\Http\Response
     */
    public function getResponse()
    {
        $response = Context::response();
        return $response;
    }
}