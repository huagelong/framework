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

namespace Trensy\Di;


interface DiInterface
{

    public static function set($name, $options);

    public static function get($name);

    public static function setNoShare($name, $options);

}