<?php
/**
 * User: Peter Wang
 * Date: 16/9/26
 * Time: 上午10:41
 */

namespace Trendi\Job;

use Trendi\Foundation\Application;
use Trendi\Server\ProcessServer;
use Trendi\Foundation\Storage\Redis;

class JobServer
{
    private $config = [];

    public function __construct(array $config, $root)
    {
        $this->config = $config;
        $obj = new Application($root);
        $obj->bootstrap();
    }

    public function clear()
    {
        $perform = $this->config['perform'];
        $storage = new Redis();
        foreach ($perform as $queueName=>$v){
            $key = Job::JOB_KEY_PRE . ":" . $queueName;
            $storage->del($key);
        }
    }

    public function start()
    {
        $name = isset($this->config['server']['name']) ? $this->config['server']['name'] : "trendi";
        $serverName = $name . "-job-server";
        swoole_set_process_name($serverName);
        echo "[$serverName] start ...\n";
        //start job run
        $job = new Job($this->config,$serverName);
        $perform = $this->config['perform'];
         
        $processServer = new ProcessServer($this->config['server']);
        $name = isset($this->config['server']['name']) ? $this->config['server']['name'] : "trendi";
        $name = $name."-job-worker";

        foreach ($perform as $key=>$v){
            $processServer->add(
                function (\swoole_process $worker) use ($key, $job, $name){
                    $worker->name($name);
                    echo "[$name] start ...\n";
                    $job->start($key);
                }
            );
        }
    }
}