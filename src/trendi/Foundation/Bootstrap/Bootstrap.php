<?php
/**
 *  初始化
 * User: Peter Wang
 * Date: 16/9/8
 * Time: 下午5:27
 */

namespace Trendi\Foundation\Bootstrap;

use Trendi\Config\Config;
use Trendi\Di\Di;
use Trendi\Foundation\Bootstrap\Config\AliasConfig;
use Trendi\Foundation\Bootstrap\Config\DiConfig;
use Trendi\Foundation\Bootstrap\Config\TaskConfig;
use Trendi\Server\Task;
use Trendi\Support\AliasLoader;
use Trendi\Support\Arr;
use Trendi\Support\Facade;
use Trendi\Support\RunMode;

class Bootstrap
{
    protected static $instance = [];

    /**
     *  instance
     * @return object
     */
    public static function getInstance($path)
    {
        if (isset(self::$instance[$path]) && self::$instance[$path]) return self::$instance[$path];

        return self::$instance[$path] = new self($path);
    }

    /**
     * Init constructor.
     */
    public function __construct($path)
    {
        $this->initEnv();
        $this->initConfig($path);
        $this->iniSet();
        $this->initMonitor();
        $this->initAlias();
        $this->initHelper();
        $this->initDi();
        $this->initFacade();
        $this->initTask();
    }

    /**
     * 监控初始化
     */
    protected function initMonitor()
    {

    }

    /**
     * php.ini 初始化
     */
    protected function iniSet()
    {
        $configApp = Config::get("app");
        if (isset($configApp['memory_limit'])) {
            ini_set('memory_limit', $configApp['memory_limit']);
        }
        if (isset($configApp['date_default_timezone_set'])) {
            date_default_timezone_set($configApp['date_default_timezone_set']);
        } else {
            date_default_timezone_set('Asia/Shanghai');
        }

    }

    /**
     * task 初始化
     * @return bool
     */
    protected function initTask()
    {
        $options = TaskConfig::getOptions();

        $configOption = Config::get("app.task");
        if ($configOption) $options = Arr::merge($options, $configOption);

        if (!$options) return true;
        Task::setTaskConfig($options);

        return true;
    }

    /**
     * 帮助函数初始化
     */
    protected function initHelper()
    {
        if(function_exists('url') && function_exists('redis') && function_exists('config')){

        }else{
            require_once "helper.php";
        }
    }

    /**
     * 配置初始化
     * 
     * @param $path
     */
    protected function initConfig($path)
    {
        Config::setConfigPath($path . "config");
    }

    /**
     * 'init runenv
     */
    protected function initEnv()
    {
        RunMode::init();
        ErrorHandleBootstrap::getInstance();
    }


    /**
     * init alias
     * @return bool
     */
    protected function initAlias()
    {
        $options = AliasConfig::getOptions();

        $configOption = Config::get("app.aliases");
        if ($configOption) $options = Arr::merge($options, $configOption);

        if (!$options) return true;
        AliasLoader::getInstance($options)->register();

        return true;
    }

    /**
     *  facade init
     */
    protected function initFacade()
    {
        Facade::clearFacadeInstances();
        Facade::setFacadeApplication(Di::getContainer());
    }

    /**
     *  Di init
     *
     * @return bool
     */
    protected function initDi()
    {
        $options = DiConfig::getOptions();

        $configOption = Config::get("app.di");
        if ($configOption) $options = Arr::merge($options, $configOption);

        if (!$options) return true;

        foreach ($options as $k => $v) {
            Di::set($k, $v);
        }
        return true;
    }


}