<?php
/**
 * User: Peter Wang
 * Date: 16/9/13
 * Time: 下午3:38
 */

namespace Trendi\Foundation\Bootstrap;

use Trendi\Config\Config;
use Trendi\Pool\Task\Pdo;
use Trendi\Pool\Task\Redis;

class PoolBootstrap
{
    protected static $instance = null;

    /**
     *  instance
     * @return \Trendi\Foundation\Bootstrap\RouteBootstrap
     */
    public static function getInstance()
    {
        if (self::$instance) return self::$instance;

        return self::$instance = new self();
    }

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->load();
    }

    public function load()
    {
        $config = Config::get("pdo");
        if ($config) Pdo::setConfig($config);
        $config = Config::get("redis");
        if ($config) Redis::setConfig($config);
    }
}