<?php
/**
 * User: Peter Wang
 * Date: 16/9/13
 * Time: 上午9:48
 */

namespace Trendi\Mvc\View;

class View
{
    const DEFAULT_ENGINE = "blade";

    protected static $engine = self::DEFAULT_ENGINE;

    public static function setEngine($engine)
    {
        self::$engine = $engine;
    }


    public static function getViewObj()
    {
        $engine = ucfirst(self::$engine);
        $objstr = "Trendi\\Mvc\\View\\Engine\\" . $engine;
        return $objstr::getInstance();
    }

}