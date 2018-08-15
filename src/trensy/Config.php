<?php
/**
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */

namespace Trensy;

use Trensy\Config\Exception\DirNotFoundException;
use Trensy\Config\Exception\InvalidArgumentException;
use Trensy\Support\Arr;
use Trensy\Support\Dir;
use Trensy\Config\ConfigInterface;

class Config implements ConfigInterface
{

    protected static $configPath = null;
    protected static $allConfig = [];
    protected static $instance = [];
    protected static $bundles=[];

    public static function getInstance()
    {
        if(self::$instance) return self::$instance;
        self::$instance = new self;
        return self::$instance;
    }

    /**
     *  设置配置路径
     * @param $path
     */
    public static function setConfigPath($path)
    {
        self::$configPath = Dir::formatPath($path);
    }

    /**
     * 获取所有配置
     * @return array
     * @throws DirNotFoundException
     */
    public static function getAll()
    {
        if (self::$allConfig) return self::$allConfig;

        if (!self::$configPath) {
            return [];
        }

        self::$allConfig = self::getDirAll(self::$configPath);
        return self::$allConfig;
    }


    /**
     * 根据dir获取配置
     * @param $dir
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected static function getDirAll($dir)
    {
        $dir = Dir::formatPath($dir);
        $config = self::getDirConfig($dir);
        //bundle 配置
        $application = Dir::formatPath(APPLICATION_PATH);
        //获取当前文件夹子
        $subDirs = [];
        $handle = dir ($application);
        while ( $entry = $handle->read() ) {
            if (($entry != ".") && ($entry != "..")) {
                $tmpPath = $application . $entry;
                if (is_dir ($tmpPath)) {
                    $subDirs[$entry] = $tmpPath;
                }
            }
        }
       if($subDirs){
            foreach ($subDirs as $k=>$v){
                if(strpos($k, "Bundle")){
                    $bundleName = strtolower(substr($k, 0, strpos($k, "Bundle")));
                    self::$bundles[] = $bundleName;
                    $configPath = Dir::formatPath($v."/Resources/configs");
                    if(is_dir($configPath)){
                        $configTmp = self::getDirConfig($configPath);
                        if($configTmp) $config = array_merge_recursive($config, $configTmp);
                    }
                }
            }
       }

        Arr::set($config, "app.bundles", self::$bundles);
//        Log::debug($config);
        return $config;
    }

    /**
     * 获取目录配置
     *
     * @param $dir
     * @return array
     */
    protected static function getDirConfig($dir)
    {
        $dir = Dir::formatPath($dir);
        $config = [];
        if (is_dir($dir)) {
            $configFiles = Dir::glob($dir, '*.php', Dir::SCAN_BFS);
            foreach ($configFiles as $file) {
                $keyString = substr($file, strlen($dir), -4);
                if (preg_match("/_\w*/", $keyString)) continue;
                $loadedConfig = require($file);
                if($loadedConfig === true) continue;
                if (!is_array($loadedConfig)) {
                    continue;
//                    throw new InvalidArgumentException("syntax error find in config file: " . $file);
                }

                $loadedConfig = Arr::createTreeByList(explode('/', $keyString), $loadedConfig);
                $config = Arr::merge($config, $loadedConfig);
            }
        }
        return $config;
    }

    /**
     * 设置配置
     * @param $key
     * @param $value
     */
    public static function set($key, $value)
    {

        if (!self::$allConfig) {
            self::getAll();
        }

        Arr::set(self::$allConfig, $key, $value);
    }

    /**
     * 获取配置
     * @param $key
     * @param $default
     * @return array
     */
    public static function get($key, $default = null)
    {
        if (!self::$allConfig) {
            self::getAll();
        }
//        var_dump(self::$configPath);
//        var_dump(self::$allConfig);
        if (!$key) {
            return $default;
        }
        $result = Arr::get(self::$allConfig, $key, $default);
        return $result;
    }

    /**
     * 配置重新加载
     *
     */
    public static function reload(){
        self::$allConfig = null;
    }

    public function __destruct()
    {
        self::$allConfig = null;
    }
}