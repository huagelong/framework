<?php
/**
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 下午6:20
 */

namespace Trendi\Server;

use swoole_server as SwooleServer;
use Trendi\Server\Facade\Context;
use Trendi\Server\Facade\Task;
use Trendi\Support\Coroutine\Event;
use Trendi\Support\Facade;

class TcpServer
{
    /**
     * @var swooleServer
     */
    public $swooleServer;
    private $adapter;
    private $serverName;
    private $config = [];

    public function __construct(SwooleServer $swooleServer, array $config, $adapter, $tcpName, $serverName)
    {
        $this->swooleServer = $swooleServer;
        $this->swooleServer->set($config);
        $this->config = $config;
        $this->adapter = $adapter;
        $this->serverName = $serverName . "-" . $tcpName;
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
        echo $this->serverName . " manage stop ......\n";
    }

    public function onManagerStart(SwooleServer $serv)
    {
        swoole_set_process_name($this->serverName . "-manage");
        echo $this->serverName . " manage start ......\n";
    }

    public function onReceive(SwooleServer $serv, $fd, $from_id, $data)
    {
        Reload::load($this->serverName, $this->config['mem_reboot_rate']);
        try {
            $this->adapter->go($data, $serv, $fd, $from_id);
        } catch (\Exception $e) {
            dump(\Trendi\Support\Exception::formatException($e));
        } catch (\Error $e) {
            dump(\Trendi\Support\Exception::formatException($e));
        }
        Event::fire("clear");
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
        swoole_set_process_name($this->serverName . "-server");
        $pid = posix_getpid();
        $pidFile = isset($this->config["pid_file"]) ? $this->config["pid_file"] : 0;
        if ($pidFile) {
            @file_put_contents($pidFile, $pid);
        }
        echo $this->serverName . " server start ......\n";
    }

    public function onShutdown(SwooleServer $swooleServer)
    {
        echo $this->serverName . " server shutdown ...... \n";
    }

    public function onWorkerStart(SwooleServer $swooleServer, $workerId)
    {
        if ($workerId >= $this->config["worker_num"]) {
            swoole_set_process_name($this->serverName . "-task-worker");
            echo $this->serverName . " task worker start ..... \n";
        } else {
            swoole_set_process_name($this->serverName . "-worker");
            echo $this->serverName . " worker start ..... \n";
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
        echo $this->serverName . " worker stop ..... \n";
    }

    public function onWorkerError(SwooleServer $swooleServer, $workerId, $workerPid, $exitCode)
    {
        echo $this->serverName . " worker error ..... \n";
    }


}