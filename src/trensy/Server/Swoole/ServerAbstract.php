<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 2018/7/31
 * Time: 17:47
 */

namespace Trensy\Server\Swoole;

use Trensy\Config;
use swoole_http_request as SwooleHttpRequest;
use swoole_http_response as SwooleHttpResponse;
use swoole_server as SwooleServer;
use Trensy\Support\Arr;
use Trensy\Support\Exception as SupportException;
use Trensy\Log;
use Trensy\Support\Tool;
use Trensy\Di;

abstract class ServerAbstract
{
    /**
     * @var swooleServer
     */
    public $swooleServer = null;
    protected $adapter = null;
    protected $serverName = '';
    protected $config = [];

    abstract public function getPfile();

    public function __construct(SwooleServer $swooleServer=null, $serverName=null, $config=null)
    {
        $defaultConfig = [
            'daemonize' => 0,
            //worker数量，推荐设置和cpu核数相等
            'worker_num' => 2,
            "dispatch_mode" => 2,
            //reactor数量，推荐2
            'reactor_num' => 2,
            "serialization" => 1,
            //以下配置直接复制，无需改动
            'open_length_check' => 1,
            'package_length_type' => 'N',
            'package_length_offset' => 0,
            'package_body_offset' => 4,
            'package_max_length' => 8 * 1024 * 1024,//默认8M
            "pfile"=> "/tmp/{$serverName}.pid",
            'open_tcp_nodelay' => 1,
        ];

        $config = Arr::merge($defaultConfig, $config);

        if($swooleServer){
            $this->swooleServer = $swooleServer;
            $this->swooleServer->set($config);
            $this->serverName = $serverName;
        }

        $this->config = $config;
    }

    /**
     * 服务器开始
     */
    public function start()
    {
        $this->swooleServer->on('start', [$this, 'onStart']);
        $this->swooleServer->on('shutdown', [$this, 'onShutdown']);

        $this->swooleServer->on('managerStart', [$this, 'onManagerStart']);
        $this->swooleServer->on('managerStop', [$this, 'onManagerStop']);
        $this->swooleServer->on('workerStart', [$this, 'onWorkerStart']);
        $this->swooleServer->on('workerStop', [$this, 'onWorkerStop']);
        $this->swooleServer->on('workerError', [$this, 'onWorkerError']);

        if ( method_exists($this , 'onRequest') ) {
            $this->swooleServer->on('request', [$this, 'onRequest']);
        }

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

        if ( method_exists($this , 'onMessage') ) {
            $this->swooleServer->on('message' , [ $this , 'onMessage' ]);
        }

        //开启任务
        if (isset($this->config['task_worker_num']) && ($this->config['task_worker_num'] > 0)) {
            $this->swooleServer->on('Task', array($this, 'onTask'));
            $this->swooleServer->on('Finish', array($this, 'onFinish'));
        }
        $this->swooleServer->start();
    }


    public function onManagerStop(SwooleServer $serv)
    {
        Log::sysinfo($this->serverName . " manage stop ......");
    }


    public function onManagerStart(SwooleServer $serv)
    {
        Tool::set_process_name($this->serverName . "-manage");
        Log::sysinfo($this->serverName . " manage start ......");
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
            $task = Di::get(\Trensy\Server\Swoole\Task::class);
            return $task->start($data);
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
        $task = Di::get(\Trensy\Server\Swoole\Task::class);
        $task->finish($data);
    }


    public function onStart(SwooleServer $swooleServer)
    {
        Tool::set_process_name($this->serverName . "-master");
        Log::sysinfo($this->serverName . " server start ......");

        file_put_contents($this->config['pfile'], $swooleServer->master_pid . ',' . $swooleServer->manager_pid);

    }

    public function onShutdown(SwooleServer $swooleServer)
    {
        Log::sysinfo($this->serverName . " server shutdown ...... ");
    }


    public function onWorkerStop(SwooleServer $swooleServer, $workerId)
    {
        Log::sysinfo($this->serverName . " worker stop ..... ");
    }

    public function onWorkerError(SwooleServer $swooleServer, $workerId, $workerPid, $exitCode)
    {
        Log::error($this->serverName . " worker error [error_code:{$exitCode}]..... ");
    }



    public function reload()
    {
        $pids = $this->getPids();
        if(!$pids) return false;
        $managerPid = $pids[1];
        posix_kill($managerPid, SIGUSR1);
        return true;
    }

    public function stop()
    {
        $pids = $this->getPids();
        if(!$pids) return true;
        $masterPid = $pids[0];
        $managerPid = $pids[1];
        if(!\swoole_process::kill($masterPid, 0)) return true;
        //获取master进程ID
        //使用swoole_process::kill代替posix_kill
        \swoole_process::kill($masterPid);
        $timeout = 30;
        $startTime = time();
        $failTimes = 0;
        while (true) {
            //检测进程是否退出
            if(\swoole_process::kill($masterPid, 0)) {
                //判断是否超时
                if((time() - $startTime) >= $timeout) {
                    if($failTimes) return false;
                    Log::sysinfo("kill master process fail, the next step is to force kill the process");
                    //超时则强制关闭
                    \swoole_process::kill($masterPid, SIGKILL);
                    \swoole_process::kill($managerPid);
                    $failTimes = 1;
                    $startTime = time();
                }
                usleep(10000);
                continue;
            }
            return true;
        }


    }

    protected function getPids()
    {
        $pFile = $this->getPfile();
        if (is_file($pFile)) {
            // Get pid file content and parse the content
            $pidFile = file_get_contents($pFile);
            $pids = explode(',', $pidFile);
            $masterPid = $pids[0];
            $managerPid = $pids[1];
            return [$masterPid, $managerPid];
        }
        return [];
    }

    public function isRunning(): bool
    {
        $masterIsLive = false;
        $pids = $this->getPids();
        if(!$pids) return $masterIsLive;
        $masterPid = $pids[0];
        $managerPid = $pids[1];
        $masterIsLive = $masterPid && @posix_kill($managerPid, 0);
        return $masterIsLive;
    }


}