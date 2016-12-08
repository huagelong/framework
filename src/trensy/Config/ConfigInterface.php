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
 * @version         1.0.7
 */

namespace Trensy\Config;


interface ConfigInterface
{
    public static function set($name, $value);

    public static function get($name);
}