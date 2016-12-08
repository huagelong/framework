<?php
/**
 *  初始化
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Coroutine\Base;

interface CoroutineBase
{
    function send(callable $callback);

    function getResult();
}