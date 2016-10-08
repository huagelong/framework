<?php
/** 
 * 路由初始化
 * 
 * User: Peter Wang
 * Date: 16/9/13
 * Time: 下午3:38
 */

namespace Trendi\Foundation\Bootstrap;

use Route;
use Trendi\Config\Config;
use Trendi\Support\Dir;

class RouteBootstrap
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

    /**
     * 路由导入
     *
     * @throws \Trendi\Support\Exception\InvalidArgumentException
     */
    public function load()
    {
        $path = Config::get("route.config_path");

        if ($path) {
            $dir = Dir::formatPath($path);
            if (is_dir($dir)) {
                $configFiles = Dir::glob($dir, '*.php', Dir::SCAN_BFS);

                foreach ($configFiles as $file) {
                    require_once $file;
                }
            }
        }
    }
}