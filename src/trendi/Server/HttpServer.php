<?php
/**
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
use Trendi\Support\Coroutine\Event;
use Trendi\Support\Facade;

class HttpServer
{
    /**
     * @var swooleServer
     */
    public $swooleServer;
    private $adapter;
    private $serverName;
    private $config = [];

    public function __construct(SwooleServer $swooleServer, array $config, $adapter, $serverName = "trendi")
    {
        $this->swooleServer = $swooleServer;
        $this->swooleServer->set($config);
        $this->config = $config;
        $this->adapter = $adapter;
        $this->serverName = $serverName;
    }

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
        echo $this->serverName . " manage stop ......\n";
    }


    public function onManagerStart(SwooleServer $serv)
    {
        swoole_set_process_name($this->serverName . "-http-manage");
        echo $this->serverName . " http manage start ......\n";
    }

    public function onTask(SwooleServer $serv, $task_id, $from_id, $data)
    {
        try {
            return Task::go($data);
        } catch (\Exception $e) {
            $exception = \Trendi\Support\Exception::formatException($e);
            dump($exception);
            return [false, $data, $exception];
        } catch (\Error $e) {
            $exception = \Trendi\Support\Exception::formatException($e);
            dump($exception);
            return [false, $data, $exception];
        }
    }

    public function onFinish(SwooleServer $serv, $task_id, $data)
    {
        Task::finish($data);
    }

    public function onStart(SwooleServer $swooleServer)
    {
        swoole_set_process_name($this->serverName . "-http-server");
        $pid = posix_getpid();
        $pidFile = isset($this->config["pid_file"]) ? $this->config["pid_file"] : 0;
        if ($pidFile) {
            @file_put_contents($pidFile, $pid);
        }
        echo $this->serverName . " http server start ......\n";
    }

    public function onShutdown(SwooleServer $swooleServer)
    {
        echo $this->serverName . " http server shutdown ...... \n";
    }

    public function onWorkerStart(SwooleServer $swooleServer, $workerId)
    {
        if ($workerId >= $this->config["worker_num"]) {
            swoole_set_process_name($this->serverName . "-http-task-worker");
            echo $this->serverName . " http task worker start ..... \n";
        } else {
            swoole_set_process_name($this->serverName . "-http-worker");
            echo $this->serverName . " http worker start ..... \n";
        }
        $this->adapter->bootstrap();
        if (Facade::getFacadeApplication()) {
            Context::set("server", $swooleServer, true, true);
            Task::setLogPath($this->config["task_fail_log"]);
            Task::setRetryCount($this->config["task_retry_count"]);
        }
    }

    public function onWorkerStop(SwooleServer $swooleServer, $workerId)
    {
        echo $this->serverName . " http worker stop ..... \n";
    }

    public function onWorkerError(SwooleServer $swooleServer, $workerId, $workerPid, $exitCode)
    {
        echo $this->serverName . " http worker error ..... \n";
        echo "======================\n";
        echo socket_strerror($exitCode) . "\n";

        Event::fire("httpd_worker_error", [$exitCode, date('Y-m-d H:i:s')]);
    }

    public function onRequest(SwooleHttpRequest $swooleHttpRequest, SwooleHttpResponse $swooleHttpResponse)
    {
        Reload::load($this->serverName . "-http-server", $this->config['mem_reboot_rate']);
//        return $swooleHttpResponse->end("a");
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
            try {
                $content = $this->requestHtmlHandle($request, $response);
                $response->end($content);
            } catch (\Exception $e) {
                $response->status(500);
                $response->end();
                dump(\Trendi\Support\Exception::formatException($e));
            } catch (\Error $e) {
                $response->status(500);
                $response->end();
                dump(\Trendi\Support\Exception::formatException($e));
            }
            if (Facade::getFacadeApplication()) {
                Context::clear();
            }
            Event::fire("clear");
        }
    }


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