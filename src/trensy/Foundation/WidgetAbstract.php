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

use Trensy\Server\Facade\Context as FContext;

abstract class WidgetAbstract implements DocLoadInterface
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
        $request = FContext::request();
        return $request;
    }

    /**
     * @return \Trensy\Http\Response
     */
    public function getResponse()
    {
        $response = FContext::response();
        return $response;
    }
}