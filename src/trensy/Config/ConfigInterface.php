<?php
/**
 *  config
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */

namespace Trensy\Config;


interface ConfigInterface
{
    public static function set($name, $value);

    public static function get($name);
}