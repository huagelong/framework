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
 * @version         3.0.0
 */

namespace Trensy\Mvc\View;

use Trensy\Mvc\View\Engine\Bladex;

class View
{
    /**
     * 获取引擎对象
     *
     * @return mixed
     */
    public static function getViewObj()
    {
        return Bladex::getInstance();
    }

}