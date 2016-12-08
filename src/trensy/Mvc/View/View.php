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

class View
{
    const DEFAULT_ENGINE = "blade";

    protected static $engine = self::DEFAULT_ENGINE;

    /**
     * 设置引擎
     *
     * @param $engine
     */
    public static function setEngine($engine)
    {
        self::$engine = $engine;
    }

    /**
     * 获取引擎对象
     *
     * @return mixed
     */
    public static function getViewObj()
    {
        $engine = ucfirst(self::$engine);
        $objstr = "Trensy\\Mvc\\View\\Engine\\" . $engine;
        return $objstr::getInstance();
    }

}