<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 2017/9/8
 * Time: 17:41
 */

namespace Trensy\Foundation;

abstract class WidgetAbstract
{
    abstract public function perform($params);

    public function render($path, $data=[])
    {
        $controller = new Controller();
        return $controller->render($path, $data);
    }

}