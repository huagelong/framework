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
 * @version         3.0.0
 */

namespace Trensy\Foundation\Bootstrap;

use Trensy\Config;
use Trensy\Di;
use Trensy\Foundation\Bootstrap\Config\AliasConfig;
use Trensy\Foundation\Bootstrap\Config\DiConfig;
use Trensy\Foundation\Exception\InvalidArgumentException;
use Trensy\Support\AliasLoader;
use Trensy\Support\Arr;
use Trensy\Support\ElapsedTime;
use Trensy\Support\Exception;
use Trensy\Support\Facade;
use Trensy\Support\Dir;
use Trensy\Event;
use Trensy\Http\Response;
use Trensy\Controller as HttpController;
use Trensy\Log;
use Dotenv\Dotenv;

class Bootstrap
{
    protected static $instance = null;

    /**
     *  instance
     * @return object
     */
    public static function getInstance()
    {
        if (isset(self::$instance) && self::$instance) return self::$instance;
        
        return self::$instance = new self();
    }

    /**
     * Init constructor.
     */
    public function __construct()
    {
        ElapsedTime::setStartTime(ElapsedTime::SYS_START);
        $this->initConfig();
        $this->initException();
        $this->iniSet();
        $this->initLog();
        $this->initMonitor();
        $this->initAlias();
        $this->initFacade();
        $this->initDi();
        $this->init404();
        $this->initDiy();
    }

    protected function initDiy()
    {
        $config = Config::get("app.init");
        if ($config) {
            $obj = Di::get($config);
//            $obj = new $config;
            if (!method_exists($obj, "perform")) {
                throw new InvalidArgumentException(" log class perform not config ");
            }
            call_user_func_array([$obj, "perform"], []);
        }
    }

    protected function initLog()
    {
        $config = Config::get("app.log");
        if($config){
            Log::register(function($params) use ($config){
                $obj = Di::get($config);
//                $obj = new $config;
                if (!method_exists($obj, "perform")) {
                    throw new InvalidArgumentException(" log class perform not config ");
                }
                call_user_func_array([$obj, "perform"], [$params]);
            });
        }
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
                $controller->view = $params->view;
                $controller->view->msg = Exception::formatException($e);
                if($config){
                    $content = $controller->render($config);
                }else{
                    $content = "Page Not Found";
                }
                $params->status(404);
                $params->end($content);
            }else{
                //tcp
                list($server, $fd, $adapter) = $params;
                $content = "API Not Found";
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
     * 配置初始化
     *
     * @param $path
     */
    protected function initConfig()
    {
        $dotenv = new Dotenv(ROOT_PATH);
        $dotenv->load();
        Config::setConfigPath(CONFIG_PATH);
    }

    /**
     * 'init runenv
     */
    protected function initException()
    {
        $errorReportingLevel = "E_ALL ^ E_NOTICE";
        $displayErrors = false;
        $debug = Config::get("app.debug");
        if($debug){
            $errorReportingLevel = "E_ALL";
            $displayErrors = true;
        }
        ErrorHandleBootstrap::getInstance($errorReportingLevel, $displayErrors);
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