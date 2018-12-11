<?php
/**
 * 项目初始入口
 * 
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */

namespace Trensy\Foundation;

use Symfony\Component\Console\Application as CmdApplication;
use Trensy\Config;
use Trensy\Di;
use Trensy\Event;
use Trensy\Foundation\Bootstrap\Bootstrap;
use Trensy\Foundation\Bootstrap\RouteBootstrap;
use Trensy\Foundation\Command;
use Trensy\Http\Request;
use Trensy\Http\RequestAbstract;
use Trensy\Http\Response;
use Trensy\Http\ResponseAbstract;
use Trensy\Mvc\Route\Base\Exception\ResourceNotFoundException;
use Trensy\Mvc\Route\RouteMatch;
use Trensy\Support\Dir;
use Trensy\Support\Arr;
use Trensy\Log;
use Trensy\Context;
use Trensy\Support\AliasLoader;
use Trensy\Support\Exception\Page404Exception;

class Application
{
    /**
     * 获取项目根目录
     *
     * @return string
     */
    public static function getRootPath()
    {
        return Dir::formatPath(ROOT_PATH);
    }

    /**
     * Command 初始化
     *
     * @throws \Exception
     */
    public static function runCmd()
    {

        Bootstrap::getInstance();

        $commands = [
            \Trensy\Foundation\Command\Artisan\Optimize::class,
            \Trensy\Foundation\Command\Artisan\Dbsync::class
        ];

        $config = Config::get("app.command");

        if ($config) {
            $commands = array_merge($commands, $config);
        }

        $commandsTmp = [];
        foreach ($commands as $cv){
            $commandsTmp[] = Di::get($cv);
        }

        $application = new CmdApplication();
        foreach ($commandsTmp as $v) {
            $application->add($v);
        }

        $application->run();
    }

    /**
     * https server 路由开始匹配
     *
     * @param $request
     * @param $response
     * @return mixed
     */
    public static function start(Request $request, Response $response)
    {
        Context::set('request', $request);
        Context::set('response', $response);

        $url = $request->getPathInfo();
        //pathinfo 处理
        $pathinfoMiddle = Config::get("app.pathinfo");
        if($pathinfoMiddle){
            $obj = Di::get($pathinfoMiddle);
            if(!method_exists($obj, "perform")){
                throw new \Exception(" 'perform' method must defined");
            }
            $url = $obj->perform($url);
        }
        $routeObj = RouteMatch::getInstance();

        $middlewareConfig = Config::get("app.middleware");

        $routeObj->setMiddlewareConfig($middlewareConfig);

        $resut = $routeObj->run($url, $request, $response);
        Event::fire("clear");
        Event::fire("request.end");
        return $resut;
    }

    /**
     * 网页初始化
     */
    public static function runWeb(RequestAbstract $request, ResponseAbstract $response)
    {
        try{
            Bootstrap::getInstance();
            RouteBootstrap::getInstance();

            Di::set("task", ['class'=>\Trensy\Server\Task::class]);
            AliasLoader::getInstance(['Task'=>\Trensy\Server\TaskFacade::class])->register();
            $request = new Request($request);
            $response = new Response($response);
            self::start($request, $response);
        }catch (Page404Exception $e){
            Event::fire("request.end");
            Event::fire("404",[$e,"Page404Exception",$response]);
        }catch (ResourceNotFoundException $e){
            Event::fire("request.end");
            Event::fire("404",[$e,"Page404Exception",$response]);
        }
    }

}