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

namespace Trensy\Foundation\Bootstrap;

use Trensy\Config\Config;
use Trensy\Di\Di;
use Trensy\Foundation\Bootstrap\Config\AliasConfig;
use Trensy\Foundation\Bootstrap\Config\DiConfig;
use Trensy\Foundation\Bootstrap\Config\TaskConfig;
use Trensy\Server\Task;
use Trensy\Support\AliasLoader;
use Trensy\Support\Arr;
use Trensy\Support\Facade;
use Trensy\Support\RunMode;
use Trensy\Support\Dir;
use Trensy\Coroutine\Event;
use Trensy\Http\Response;
use Trensy\Foundation\Controller as HttpController;
use Trensy\Rpc\Controller as RpcController;
use Trensy\Support\Log;

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
        if(defined('APPLICATION_PATH')){
            $configPath = APPLICATION_PATH;
        }else{
            $configPath = $path;
        }
        $this->initConfig($configPath);
        $this->iniSet();
        $this->initLog();
        $this->initMonitor();
        $this->initAlias();
        $this->initHelper();
        $this->initDi();
        $this->initFacade();
        $this->initTask();
        $this->init404();
        $this->initEvent();
    }

    protected function initLog()
    {
        $config = Config::get("app.log");
        if($config){
            Log::register(function($params) use ($config){
                $obj = new $config;
                if (!method_exists($obj, "perform")) {
                    throw new InvalidArgumentException(" log class perform not config ");
                }
                call_user_func_array([$obj, "perform"], [$params]);
            });
        }
    }

    public function initEvent()
    {
        
    }

    /**
     * 404  处理
     */
    protected function init404()
    {
        Event::bind("404",function($allParams){
            list($e, $errorName, $params) = $allParams;
            $config = Config::get("app.view.page404");

            if($errorName == "Page404Exception"){
                Log::debug($e->getMessage());
            }

            if($params instanceof Response){
                $controller = new HttpController();
                if($config){
                    $content = $controller->render($config);
                }else{
                    $content = "Page Not Found";
                }
                $params->end($content);
            }else{
                list($server, $fd, $adapter) = $params;
                $controller = new RpcController();
                $content = $controller->response("", RpcController::RESPONSE_NORMAL_ERROR_CODE, "API Not Found");
                $content = $adapter->getSerialize()->format($content);
                $server->send($content);
                $server->close($fd);
            }
        });
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
        $path = Dir::formatPath($path);
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