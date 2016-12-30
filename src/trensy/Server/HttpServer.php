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
 * @version         1.0.7
 */

namespace Trensy\Server;

use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;
use swoole_http_server as SwooleServer;
use Trensy\Http\Request;
use Trensy\Http\Response;
use Trensy\Server\Facade\Context as FContext;
use Trensy\Server\Facade\Task as FacadeTask;
use Trensy\Support\Event;
use Trensy\Support\Facade;
use Trensy\Support\Log;
use Trensy\Support\Exception;
use Trensy\Support\ElapsedTime;
use Trensy\Support\Exception\RuntimeExitException;
use Trensy\Mvc\Route\Base\Exception\ResourceNotFoundException;
use Trensy\Support\Exception\Page404Exception;
use Trensy\Support\Exception as SupportException;

class HttpServer
{
    /**
     * @var swooleServer
     */
    public $swooleServer = null;
    private $adapter = null;
    private $serverName = '';
    private $config = [];

    public function __construct(SwooleServer $swooleServer, array $config, $adapter, $serverName = "trensy")
    {
        $this->swooleServer = $swooleServer;
        $this->swooleServer->set($config);
        $this->config = $config;
        $this->adapter = $adapter;
        $this->serverName = $serverName."-httpd";
        $this->config['server_name'] =$this->serverName;
    }

    /**
     * 服务器开始
     */
    public function start()
    {
        $this->swooleServer->on('start', [$this, 'onStart']);
        $this->swooleServer->on('shutdown', [$this, 'onShutdown']);

        $this->swooleServer->on('managerStart', [$this, 'onManagerStart']);
        $this->swooleServer->on('managerStop', [$this, 'onManagerSop']);
        $this->swooleServer->on('workerStart', [$this, 'onWorkerStart']);
        $this->swooleServer->on('workerStop', [$this, 'onWorkerStop']);
        $this->swooleServer->on('workerError', [$this, 'onWorkerError']);

        $this->swooleServer->on('request', [$this, 'onRequest']);

        if ( method_exists($this , 'onOpen') ) {
            $this->swooleServer->on('open' , [ $this , 'onOpen' ]);
        }
        if ( method_exists($this , 'onClose') ) {
            $this->swooleServer->on('close' , [ $this , 'onClose' ]);
        }

        if ( method_exists($this , 'onWsHandshake') ) {
            $this->swooleServer->on('handshake' , [ $this , 'onWsHandshake' ]);
        }
        if ( method_exists($this , 'onWsMessage') ) {
            $this->swooleServer->on('message' , [ $this , 'onWsMessage' ]);
        }
        
        //开启任务
        if (isset($this->config['task_worker_num']) && ($this->config['task_worker_num'] > 0)) {
            $this->swooleServer->on('Task', array($this, 'onTask'));
            $this->swooleServer->on('Finish', array($this, 'onFinish'));
        }
        $this->swooleServer->start();
    }

    public function onManagerSop(SwooleServer $serv)
    {
        Log::sysinfo($this->serverName . " manage stop ......");
    }


    public function onManagerStart(SwooleServer $serv)
    {
        swoole_set_process_name($this->serverName . "-manage");
        Log::sysinfo($this->serverName . " manage start ......");

        $memRebootRate = isset($this->config['mem_reboot_rate'])?$this->config['mem_reboot_rate']:0;

        Reload::load($this->serverName , $memRebootRate, $this->config);

    }

    /**
     * 进程task
     *
     * @param SwooleServer $serv
     * @param $task_id
     * @param $from_id
     * @param $data
     * @return array
     */
    public function onTask(SwooleServer $serv, $task_id, $from_id, $data)
    {
        try {
            return FacadeTask::start($data);
        } catch (\Exception $e) {
            $exception = SupportException::formatException($e);
            Log::error($exception);
            return [false, $data, $exception];
        } catch (\Error $e) {
            $exception = SupportException::formatException($e);
            Log::error($exception);
            return [false, $data, $exception];
        }
    }

    public function onFinish(SwooleServer $serv, $task_id, $data)
    {
        FacadeTask::finish($data);
    }


    public function onStart(SwooleServer $swooleServer)
    {
        swoole_set_process_name($this->serverName . "-master");
        Log::sysinfo($this->serverName . " server start ......");
    }

    public function onShutdown(SwooleServer $swooleServer)
    {
        Log::sysinfo($this->serverName . " server shutdown ...... ");
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

        Task::setConfig($this->config);

        if ($workerId >= $this->config["worker_num"]) {
            swoole_set_process_name($this->serverName . "-task-worker");
            Log::sysinfo($this->serverName . " task worker start ..... ");
        } else {
            swoole_set_process_name($this->serverName . "-worker");
            Log::sysinfo($this->serverName . " worker start ..... ");
        }
        $this->adapter->httpBoostrap();

        if (Facade::getFacadeApplication()) {
            FContext::set("server", $swooleServer, true, true);
        }
    }

    public function onWorkerStop(SwooleServer $swooleServer, $workerId)
    {
        Log::sysinfo($this->serverName . " worker stop ..... ");
    }

    public function onWorkerError(SwooleServer $swooleServer, $workerId, $workerPid, $exitCode)
    {
        Log::sysinfo($this->serverName . " worker error ..... ");
        Log::sysinfo("======================");
        Log::error(socket_strerror($exitCode) . "");
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
        ElapsedTime::setStartTime("sys_elapsed_time");
        
        $request = new Request($swooleHttpRequest);
        $response = new Response($swooleHttpResponse);
        
        if (Facade::getFacadeApplication()) {
            FContext::clear();
            FContext::set("response", $response);
            FContext::set("request", $request);
            $request = FContext::request();
            $response = FContext::response();
        }

        $httpSendFile = new HttpSendFile($request, $response);
        $httpSendFile->setConfig($this->config);
        list($isFile,,,,) = $httpSendFile->analyse();

        if ($isFile) {
            $httpSendFile->sendFile();
        } else {
            $this->response($request, $response);
            if (Facade::getFacadeApplication()) {
                FContext::clear();
            }
            Event::fire("clear");
        }
    }

    private function response(Request $request, Response $response)
    {
        $workerId = posix_getpid();
        try {
            $this->requestHtmlHandle($request, $response);
            Event::fire("request.end",$workerId);
        }catch (Page404Exception $e){
            Event::fire("request.end",$workerId);
            Event::fire("404",[$e,"Page404Exception",$response]);
        }catch (ResourceNotFoundException $e){
            Event::fire("request.end",$workerId);
            Event::fire("404",[$e,"ResourceNotFoundException",$response]);
        }catch (RuntimeExitException $e){
            Event::fire("request.end",$workerId);
            Log::sysinfo("RuntimeExitException:".$e->getMessage());
        }catch (\Exception $e) {
            Event::fire("request.end",$workerId);
            Log::error(Exception::formatException($e));
            $response->status(500);
            $response->end();
        } catch (\Error $e) {
            Event::fire("request.end",$workerId);
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
        $gzip = isset($this->config["gzip"]) ? $this->config["gzip"] : 0;
        if ($gzip) {
            $response->gzip($gzip);
        }

        $response->header("Content-Type", "text/html;charset=utf-8");
        return $this->adapter->start($request, $response);
    }

}