<?php
/**
 * socket server 
 * 
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 下午6:20
 */

namespace Trendi\Server;

use swoole_server as SwooleServer;
use Trendi\Server\Facade\Context;
use Trendi\Server\Facade\Task;
use Trendi\Coroutine\Event;
use Trendi\Support\Facade;
use Trendi\Support\Exception as ExceptionFormat;
use Trendi\Support\Log;

class SocketServer
{
    /**
     * @var swooleServer
     */
    public $swooleServer;
    private $adapter;
    private $serverName;
    private $config = [];

    public function __construct(SwooleServer $swooleServer, array $config, $adapter, $socketName, $serverName)
    {
        $this->swooleServer = $swooleServer;
        $this->swooleServer->set($config);
        $this->config = $config;
        $this->adapter = $adapter;
        $this->serverName = $serverName . "-" . $socketName;
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
        $this->swooleServer->on('receive', [$this, 'onReceive']);

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
    }

    public function onReceive(SwooleServer $serv, $fd, $from_id, $data)
    {
        Reload::load($this->serverName . "-server", $this->config['mem_reboot_rate']);
        try {
            $this->adapter->perform($data, $serv, $fd, $from_id);
        } catch (\Exception $e) {
            Log::error(ExceptionFormat::formatException($e));
        } catch (\Error $e) { //php7.0兼容
            Log::error(ExceptionFormat::formatException($e));
        }
        Event::fire("clear");
    }

    public function onTask(SwooleServer $serv, $task_id, $from_id, $data)
    {
        try {
            return Task::start($data);
        } catch (\Exception $e) {
            $exception = ExceptionFormat::formatException($e);
            Log::error($exception);
            return [false, $data, $exception];
        } catch (\Error $e) {
            $exception = ExceptionFormat::formatException($e);
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
        swoole_set_process_name($this->serverName . "-server");
        Log::sysinfo($this->serverName . " server start ......");
    }

    public function onShutdown(SwooleServer $swooleServer)
    {
        Log::sysinfo($this->serverName . " server shutdown ...... ");
    }

    public function onWorkerStart(SwooleServer $swooleServer, $workerId)
    {
        if ($workerId >= $this->config["worker_num"]) {
            swoole_set_process_name($this->serverName . "-task-worker");
            Log::sysinfo($this->serverName . " task worker start ..... ");
        } else {
            swoole_set_process_name($this->serverName . "-worker");
            Log::sysinfo($this->serverName . " worker start ..... ");
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
        Log::sysinfo($this->serverName . " worker stop ..... ");
    }

    public function onWorkerError(SwooleServer $swooleServer, $workerId, $workerPid, $exitCode)
    {
        Log::sysinfo($this->serverName . " worker error .....");
    }


}