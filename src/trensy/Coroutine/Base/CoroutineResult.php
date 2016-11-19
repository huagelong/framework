<?php
/**
 *  初始化
 * User: Peter Wang
 * Date: 16/9/8
 * Time: 下午5:27
 */

namespace Trensy\Coroutine\Base;


class CoroutineResult
{
    private static $instance;

    public function __construct()
    {
        self::$instance = &$this;
    }

    public static function &getInstance()
    {
        if (self::$instance == null) {
            new CoroutineResult();
        }
        return self::$instance;
    }
}