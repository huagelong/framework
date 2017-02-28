<?php
/**
 * socket server
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

use swoole_server as SwooleServer;
use Trensy\Config\Config;
use Trensy\Support\Event;
use Trensy\Mvc\Route\Base\Exception\ResourceNotFoundException;
use Trensy\Server\Facade\Context as FContext;
use Trensy\Server\Facade\Task as FacadeTask;
use Trensy\Support\ElapsedTime;
use Trensy\Support\Exception as ExceptionFormat;
use Trensy\Support\Exception\Page404Exception;
use Trensy\Support\Exception\RuntimeExitException;
use Trensy\Support\Facade;
use Trensy\Support\Log;

class SocketServer
{
    /**
     * @var swooleServer
     */
    public $swooleServer;
    protected $adapter;
    protected $serverName;
    protected $config = [];

    public function __construct(SwooleServer $swooleServer, array $config, $adapter, $socketName, $serverName)
    {
        $this->swooleServer = $swooleServer;
        $this->swooleServer->set($config);
        $this->config = $config;
        $this->adapter = $adapter;
        $this->serverName = $serverName . "-" . $socketName;
        $this->config['server_name'] = $this->serverName;
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

        $memRebootRate = isset($this->config['mem_reboot_rate']) ? $this->config['mem_reboot_rate'] : 0;

        Reload::load($this->serverName . "-master", $memRebootRate, $this->config);
    }

    public function onReceive(SwooleServer $serv, $fd, $from_id, $data)
    {
        ElapsedTime::setStartTime("sys_elapsed_time");
        $workerId = posix_getpid();
        try {
            $this->adapter->perform($data, $serv, $fd, $from_id);
            Event::fire("request.end",$workerId);
        } catch (Page404Exception $e) {
            Event::fire("request.end",$workerId);
            Event::fire("404", [$e, "Page404Exception", [$serv, $fd, $this->adapter]]);
        } catch (ResourceNotFoundException $e) {
            Event::fire("request.end",$workerId);
            Event::fire("404", [$e, "ResourceNotFoundException", [$serv, $fd, $this->adapter]]);
        } catch (RuntimeExitException $e) {
            Event::fire("request.end",$workerId);
            Log::sysinfo("RuntimeExitException:" . $e->getMessage());
        } catch (\Exception $e) {
            Event::fire("request.end",$workerId);
            Log::error(ExceptionFormat::formatException($e));
        } catch (\Error $e) { //php7.0兼容
            Event::fire("request.end",$workerId);
            Log::error(ExceptionFormat::formatException($e));
        }
        Event::fire("clear");
    }

    public function onTask(SwooleServer $serv, $task_id, $from_id, $data)
    {
        try {
            $result = FacadeTask::start($data);
            return [true, $result, ''];
        } catch (RuntimeExitException $e) {
            Log::sysinfo("RuntimeExitException:" . $e->getMessage());
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

        $this->adapter->bootstrap();
        if (Facade::getFacadeApplication()) {
            FContext::set("server", $swooleServer, true, true);
        }
        //配置重载
        Config::reload();
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