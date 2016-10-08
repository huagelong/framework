<?php
/**
 * httpd 服务器
 *
 * User: Peter Wang
 * Date: 16/9/14
 * Time: 上午10:04
 */

namespace Trendi\Server;

use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;
use swoole_http_server as SwooleServer;
use Trendi\Http\Request;
use Trendi\Http\Response;
use Trendi\Server\Facade\Context;
use Trendi\Server\Facade\Task;
use Trendi\Coroutine\Event;
use Trendi\Support\Facade;
use Trendi\Support\Log;
use Trendi\Support\Exception;

class HttpServer
{
    /**
     * @var swooleServer
     */
    public $swooleServer = null;
    private $adapter = null;
    private $serverName = '';
    private $config = [];
    private $scheduler = null;

    public function __construct(SwooleServer $swooleServer, array $config, $adapter, $serverName = "trendi")
    {
        $this->swooleServer = $swooleServer;
        $this->swooleServer->set($config);
        $this->config = $config;
        $this->adapter = $adapter;
        $this->serverName = $serverName;
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
        swoole_set_process_name($this->serverName . "-httpd-manage");
        Log::sysinfo($this->serverName . " httpd manage start ......");
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
            return Task::start($data);
        } catch (\Exception $e) {
            $exception = \Trendi\Support\Exception::formatException($e);
            Log::error($exception);
            return [false, $data, $exception];
        } catch (\Error $e) {
            $exception = \Trendi\Support\Exception::formatException($e);
            Log::error($exception);
            return [false, $data, $exception];
        }
    }

    public function onFinish(SwooleServer $serv, $task_id, $data)
    {
        Task::finish($data);
    }


    public function onStart(SwooleServer $swooleServer)
    {
        swoole_set_process_name($this->serverName . "-httpd-server");
        Log::sysinfo($this->serverName . " httpd server start ......");
    }

    public function onShutdown(SwooleServer $swooleServer)
    {
        Log::sysinfo($this->serverName . " httpd server shutdown ...... ");
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

        if (function_exists("opcache_reset")) {
            opcache_reset();
        }

        if ($workerId >= $this->config["worker_num"]) {
            swoole_set_process_name($this->serverName . "-httpd-task-worker");
            Log::sysinfo($this->serverName . " httpd task worker start ..... ");
        } else {
            swoole_set_process_name($this->serverName . "-httpd-worker");
            Log::sysinfo($this->serverName . " httpd worker start ..... ");
        }
        $this->adapter->httpBoostrap();

        if (Facade::getFacadeApplication()) {
            Context::set("server", $swooleServer, true, true);
            Task::setLogPath($this->config["task_fail_log"]);
            Task::setRetryCount($this->config["task_retry_count"]);
        }
    }

    public function onWorkerStop(SwooleServer $swooleServer, $workerId)
    {
        Log::sysinfo($this->serverName . " httpd worker stop ..... ");
    }

    public function onWorkerError(SwooleServer $swooleServer, $workerId, $workerPid, $exitCode)
    {
        Log::sysinfo($this->serverName . " httpd worker error ..... ");
        Log::sysinfo("======================");
        Log::error(socket_strerror($exitCode) . "");

        Event::fire("httpd_worker_error", [$exitCode, date('Y-m-d H:i:s')]);
    }

    /**
     * 请求处理
     *
     * @param SwooleHttpRequest $swooleHttpRequest
     * @param SwooleHttpResponse $swooleHttpResponse
     * @throws Exception\InvalidArgumentException
     * @throws \Trendi\Http\Exception\ContextErrorException
     */
    public function onRequest(SwooleHttpRequest $swooleHttpRequest, SwooleHttpResponse $swooleHttpResponse)
    {
        Reload::load($this->serverName . "-httpd-server", $this->config['mem_reboot_rate']);

        $request = new Request($swooleHttpRequest);
        $response = new Response($swooleHttpResponse);

        if (Facade::getFacadeApplication()) {
            Context::clear();
            Context::set("response", $response);
            Context::set("request", $request);
            $request = Context::request();
            $response = Context::response();
        }

        $httpSendFile = new HttpSendFile($request, $response);
        $httpSendFile->setConfig($this->config);
        list($isFile, , ,) = $httpSendFile->analyse();
        if ($isFile) {
            $httpSendFile->sendFile();
        } else {
            $this->response($request, $response);
            if (Facade::getFacadeApplication()) {
                Context::clear();
            }
            Event::fire("clear");
        }
    }

    private function response(Request $request, Response $response)
    {
        try {
            $content = $this->requestHtmlHandle($request, $response);
            $response->end($content);
        } catch (\Exception $e) {
            $response->status(500);
            $response->end();
            Log::error(Exception::formatException($e));
        } catch (\Error $e) {
            $response->status(500);
            $response->end();
            Log::error(Exception::formatException($e));
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