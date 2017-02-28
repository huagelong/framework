<?php
/**
 * job server
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Job;

use Trensy\Foundation\Bootstrap\Facade\Job as FJob;
use Trensy\Foundation\Storage\Redis;
use Trensy\Server\ProcessServer;
use Trensy\Support\Log;

class JobServer extends ProcessServer
{
    protected static $workerMap = [];
    protected static $baseName = "";
    protected static $asDaemon = 1;

    public function __construct(array $config, $root)
    {
        $this->config = $config;
        $name = isset($this->config['server']['name']) ? $this->config['server']['name'] : "trensy";
        $this->config['server_name'] = $name;
        $asDaemon = isset($this->config['server']['daemonize']) ? $this->config['server']['daemonize'] : 0;
        self::$asDaemon = $asDaemon;
        parent::__construct($asDaemon, false);
        self::$baseName = $name."-job";
    }

    /**
     * 清空所有job
     */
    public function clear($isInit=0)
    {
        $perform = $this->config['perform'];
        $storage = new Redis();
        foreach ($perform as $queueName => $v) {
            $key = Job::JOB_KEY_PRE . ":" . $queueName;
            if($isInit){
                $key = "INIT_".$key;
            }
            $storage->del($key);
        }
    }

    /**
     * job 服务开始
     */
    public function start()
    {
        $serverName = self::$baseName . "-master";
        swoole_set_process_name($serverName);

        $perform = $this->config['perform'];

        //载入初始化job
        $this->clear(1);
        $jobs = isset($this->config['jobs']) ? $this->config['jobs'] : null;
        if ($jobs) {
            Log::sysinfo("loading init jobs ...");
            foreach ($jobs as $k => $v) {
                $obj = array_isset($v, 0);
                $startTime = array_isset($v, 1);
                $cronStr = array_isset($v, 2);
                FJob::add($k, $obj, $startTime, $cronStr, 1);
            }
        }

        $job = new Job($this->config);
        $name = self::$baseName . "-worker";

        foreach ($perform as $key => $v) {
            $this->createProcess($key, $name, $job);
        }
    }

    /**
     * 创建新的进程
     *
     * @param $key
     * @param $job
     * @param $name
     */
    public function createProcess($key, $name, $job)
    {
        $pid = $this->add(
            function (\swoole_process $worker) use ($key, $job, $name) {
                $tmpName = $name."-".$key;
                $worker->name($tmpName);
                if(!self::$asDaemon)  Log::sysinfo("$tmpName start ...");
                $job->start($key);
            }
        );
        self::$workerMap[$key] = $pid;
    }

    /**
     * 收到进程异常信号,重新创建进程
     *
     */
    public function sigchld()
    {
        \swoole_process::signal(SIGCHLD, function () {
            $job = new Job($this->config);
            $name = self::$baseName . "-worker";

            if($ret = \swoole_process::wait(false)) {
                $pid = $ret['pid'];
                $this->unsetWorker($pid);
                $neworkerMap = array_flip(self::$workerMap);
                if(isset($neworkerMap[$pid])){
                    $key = $neworkerMap[$pid];
                    $this->createProcess($key, $name, $job);
                }
            }
        });
    }
}