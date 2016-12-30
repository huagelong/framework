<?php
/**
 * 模板
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

namespace Trensy\Mvc\View;

use Trensy\Mvc\View\Engine\Blade;

class View
{
    /**
     * 获取引擎对象
     *
     * @return mixed
     */
    public static function getViewObj()
    {
        return Blade::getInstance();
    }

}