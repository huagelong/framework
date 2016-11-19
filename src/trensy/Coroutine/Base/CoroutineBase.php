<?php
/**
 *  初始化
 * User: Peter Wang
 * Date: 16/9/8
 * Time: 下午5:27
 */

namespace Trensy\Coroutine\Base;

interface CoroutineBase
{
    function send(callable $callback);

    function getResult();
}