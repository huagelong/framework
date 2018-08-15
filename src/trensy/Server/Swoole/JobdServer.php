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
 * @version         3.0.0
 */

namespace Trensy\Server\Swoole;

use swoole_server as SwooleServer;
use Trensy\Config;
use Trensy\Di;
use Trensy\Context;
use Trensy\Event;
use Trensy\Log;
use Trensy\Support\Arr;
use Trensy\Support\Tool;

class JobdServer extends ServerAbstract
{
    protected $jobList = [];

    public function __construct(SwooleServer $swooleServer=null)
    {
        $serverName = Config::get("app.app_name")."-jobd";
        $defaultConfig = [
            'daemonize' => 0,
            //worker数量，推荐设置和cpu核数相等
            "dispatch_mode" => 2,
            //reactor数量，推荐2
            'reactor_num' => 2,
            "task_worker_num" => 5,
            "task_retry_count" => 2,
            "serialization" => 1,
            //以下配置直接复制，无需改动
            'open_length_check' => 1,
            'package_length_type' => 'N',
            'package_length_offset' => 0,
            'package_body_offset' => 4,
            'package_max_length' => 8 * 1024 * 1024,//默认8M
            "pfile"=> "/tmp/{$serverName}_pid",
            'open_tcp_nodelay' => 1,
        ];

        $config = Config::get("swoole.jobd");
        $config = Arr::merge($defaultConfig, $config);

        $this->jobList = $this->getjobList();

        if(!$this->jobList){
            Log::show(" no jobs");
            exit;
        }
    
        Log::sysinfo("jobs:".json_encode($this->jobList));

        $config['worker_num'] = count($this->jobList);

        parent::__construct($swooleServer, $serverName, $config);
    }

    public function getPfile()
    {
        return $this->config['pfile'];
    }

    public function start()
    {
        $this->swooleServer->on('start', [$this, 'onStart']);
        $this->swooleServer->on('shutdown', [$this, 'onShutdown']);

        $this->swooleServer->on('managerStart', [$this, 'onManagerStart']);
        $this->swooleServer->on('managerStop', [$this, 'onManagerStop']);
        $this->swooleServer->on('workerStart', [$this, 'onWorkerStart']);
        $this->swooleServer->on('workerStop', [$this, 'onWorkerStop']);
        $this->swooleServer->on('workerError', [$this, 'onWorkerError']);

        $this->swooleServer->on('receive', [$this, 'onReceive']);

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

    protected function getjobList(){
        $jobList = Config::get("app.jobd");
        $realip = swoole_get_local_ip();
        $realip = current($realip);
        Log::sysinfo("local ip :". $realip);
        if($jobList){
            foreach ($jobList as &$v){
                $ips = isset($v['ip'])?$v['ip']:null;
                if($ips){
                    if(!in_array($realip, $ips)){
                        unset($v);
                        Log::sysinfo("local ip not allow run job");
                        continue;
                    }
                }
            }
        }

        return $jobList;
    }

    public function onReceive(){}

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

        if ($workerId >= $this->config["worker_num"]) {
            Tool::set_process_name($this->serverName . "-task-worker");
            Log::sysinfo($this->serverName . " task worker start ..... ");
        } else {
            Tool::set_process_name($this->serverName . "-worker");
            Log::sysinfo($this->serverName . " worker start ..... ");
            //运行秒表
//            Log::debug($this->jobList);
            $task = $this->jobList[$workerId];
            swoole_timer_tick(1000, function()use($task){
                $this->run($task);
            });
        }

        Context::set("swlserver", $swooleServer, false, true);
    }

    protected function run($pv){

        $rule = isset($pv['rule']) ? $pv['rule'] : null;
        $start = isset($pv['start']) ? $pv['start'] : null;
        $end = isset($pv['end']) ? $pv['end'] : null;

        if(!$rule) return $rule;

        if($start && (time() < strtotime($start))) return ;
        if($end && (time() > strtotime($end))) return ;

        if(date('Y-m-d H:i:s') != date($rule)){
            return ;
        }

        $class = isset($pv['class']) && $pv['class']?$pv['class']:null;
        if(!$class) return ;

        $taskObj = Di::get($class);

        if (!is_object($taskObj)) {
            Log::error("jobObj unvalidate :" . $class);
        }

        $taskObj->perform();
        return true;
    }

}