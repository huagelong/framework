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

class JobServer
{
    private $config = [];

    public function __construct(array $config, $root)
    {
        $this->config = $config;
    }

    /**
     * 清空所有job
     */
    public function clear()
    {
        $perform = $this->config['perform'];
        $storage = new Redis();
        foreach ($perform as $queueName => $v) {
            $key = Job::JOB_KEY_PRE . ":" . $queueName;
            $storage->del($key);
        }
    }

    /**
     * job 服务开始
     */
    public function start()
    {
        $name = isset($this->config['server']['name']) ? $this->config['server']['name'] : "trensy";
        $preName = $name."-job";
        $serverName = $preName . "-master";
        swoole_set_process_name($serverName);
//        Log::sysinfo("[$serverName] start ...");
        //start job run
        $this->config['server_name'] = $name;
        $job = new Job($this->config);
        $perform = $this->config['perform'];

        $processServer = new ProcessServer($this->config['server'], false);
        $name = $preName . "-worker";

        //载入初始化job
        $jobs = isset($this->config['jobs']) ? $this->config['jobs'] : null;
        if ($jobs) {
            Log::sysinfo("loading init jobs ...");
            foreach ($jobs as $k => $v) {
                $obj = array_isset($v, 0);
                $startTime = array_isset($v, 1);
                $cronStr = array_isset($v, 2);
                $tagName = array_isset($v, 3);
                $tagName = $tagName?$tagName:$k;
                FJob::add($k, $obj, $startTime, $cronStr, $tagName);
            }
        }
        foreach ($perform as $key => $v) {
            $processServer->add(
                function (\swoole_process $worker) use ($key, $job, $name) {
                    $worker->name($name);
                    Log::sysinfo("$name start ...");
                    $job->start($key);
                }
            );
        }
    }
}