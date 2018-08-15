<?php
/**
 * httpd 服务器
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

namespace Trensy\Server\Swoole;

use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;
use swoole_http_server as SwooleServer;
use Trensy\Config;
use Trensy\Di;
use Trensy\Foundation\Application;
use Trensy\Foundation\Bootstrap\Bootstrap;
use Trensy\Foundation\Bootstrap\RouteBootstrap;
use Trensy\Http\Request;
use Trensy\Http\Response;
use Trensy\Context;
use Trensy\Event;
use Trensy\Log;
use Trensy\Support\Arr;
use Trensy\Support\Exception;
use Trensy\Support\ElapsedTime;
use Trensy\Support\Exception\RuntimeExitException;
use Trensy\Mvc\Route\Base\Exception\ResourceNotFoundException;
use Trensy\Support\Exception\Page404Exception;
use Trensy\Support\Exception as SupportException;
use Trensy\Support\Tool;

class HttpdServer extends ServerAbstract
{

    public function __construct(SwooleServer $swooleServer=null)
    {
        $serverName = Config::get("app.app_name")."-http";;
        $defaultConfig = [
            'daemonize' => 0,
            //worker数量，推荐设置和cpu核数相等
            'worker_num' => 2,
            "dispatch_mode" => 2,
            //reactor数量，推荐2
            'reactor_num' => 2,
            "gzip" => 4,
            "task_worker_num" => 5,
            "task_retry_count" => 2,
            "serialization" => 1,
            //以下配置直接复制，无需改动
            'open_length_check' => 1,
            'package_length_type' => 'N',
            'package_length_offset' => 0,
            'package_body_offset' => 4,
            'package_max_length' => 8 * 1024 * 1024,//默认8M
            "pfile"=> "/tmp/{$serverName}_pid",
            'open_tcp_nodelay' => 1,
        ];

        $config = Config::get("swoole.httpd");
        $config = Arr::merge($defaultConfig, $config);

        parent::__construct($swooleServer, $serverName, $config);
    }

    public function getPfile()
    {
        return $this->config['pfile'];
    }


    /**
     * 数据初始化
     *
     * @param SwooleServer $swooleServer
     * @param $workerId
     */
    public function onWorkerStart(SwooleServer $swooleServer, $workerId)
    {
        if (function_exists("apc_clear_cache")) {
            apc_clear_cache();
        }

        if (function_exists("apcu_clear_cache")) {
            apcu_clear_cache();
        }

        if (function_exists("opcache_reset")) {
            opcache_reset();
        }

        if ($workerId >= $this->config["worker_num"]) {
            Tool::set_process_name($this->serverName . "-task-worker");
            Log::sysinfo($this->serverName . " task worker start ..... ");
        } else {
            Tool::set_process_name($this->serverName . "-worker");
            Log::sysinfo($this->serverName . " worker start ..... ");
        }

        RouteBootstrap::getInstance();

        Context::set("swlserver", $swooleServer, false, true);
    }



    /**
     * 请求处理
     *
     * @param SwooleHttpRequest $swooleHttpRequest
     * @param SwooleHttpResponse $swooleHttpResponse
     * @throws Exception\InvalidArgumentException
     * @throws \Trensy\Http\Exception\ContextErrorException
     */
    public function onRequest(SwooleHttpRequest $swooleHttpRequest, SwooleHttpResponse $swooleHttpResponse)
    {
        ElapsedTime::setStartTime(ElapsedTime::SYS_START);

        $gzip = isset($this->config["gzip"]) ? $this->config["gzip"] : 0;

        $httpRequest = new HttpRequest($swooleHttpRequest);
        $httpResponse = new HttpResponse($swooleHttpResponse);

        $request = new Request($httpRequest);
        $response = new Response($httpResponse, $gzip);

        //清空上下文
        Context::clear();

        $httpSendFile = new HttpSendFile($request, $response);
        list($isFile,,,,) = $httpSendFile->analyse();

        if ($isFile) {
            $httpSendFile->sendFile();
        } else {
            $this->response($request, $response);
        }

        Event::fire("clear");
    }

    protected function response(Request $request, Response $response)
    {
        try {
            $this->requestHtmlHandle($request, $response);
            Event::fire("request.end");
        }catch (Page404Exception $e){
            Event::fire("request.end");
            Event::fire("404",[$e,"Page404Exception",$response]);
        }catch (ResourceNotFoundException $e){
            Event::fire("request.end");
            Event::fire("404",[$e,"ResourceNotFoundException",$response]);
        }catch (RuntimeExitException $e){
            Event::fire("request.end");
            Log::sysinfo("RuntimeExitException:".$e->getMessage());
        }catch (\Exception $e) {
            Event::fire("request.end");
            Log::error(Exception::formatException($e));
            $response->status(500);
            $response->end();
        } catch (\Error $e) {
            Event::fire("request.end");
            Log::error(Exception::formatException($e));
            $response->status(500);
            $response->end();
        }
    }

    /**
     *  内容处理
     *
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    protected function requestHtmlHandle(Request $request, Response $response)
    {
        $response->header("Content-Type", "text/html;charset=utf-8");
        return Application::start($request, $response);
    }

}