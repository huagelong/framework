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


use Trensy\Config;
use Trensy\Context;
use Trensy\Di;
use Trensy\Event;
use Trensy\Foundation\Exception\InvalidArgumentException;
use Trensy\Server\TaskRunAbstract;
use Trensy\Server\Swoole\Timer;
use Trensy\Log;

class JobTask extends TaskRunAbstract
{
    private  $retryCount = 2;
    private  $logPath = "/tmp/jobtaskFail.log";
    private static $numbersTmp = [];
    protected  $timeOut = 3;

    public function __construct()
    {
        $taskConfig = Config::get("swoole.jobd");
        $this->retryCount = $taskConfig['task_retry_count'];
        $this->logPath = $taskConfig['task_fail_log'];
        $this->timeOut = $taskConfig['task_timeout'];
    }


    public function start($data)
    {

        list($task, $params) = $data;
        if (is_string($task)) {
            $taskConfig = Config::get("app.task");
            $taskClass = isset($taskConfig[$task]) ? $taskConfig[$task] : null;
            if (!$taskClass) {
                throw new InvalidArgumentException(" task not config ");
            }

            $obj = Di::get($taskClass);

            if (!method_exists($obj, "perform")) {
                throw new InvalidArgumentException(" task method perform not config ");
            }
            $result = call_user_func_array([$obj, "perform"], $params);
            return [true, $result,''];
        }
        return [true, '',''];
    }

    public function finish($data)
    {
        list($status, $returnData, $exception) = $data;

        //如果执行不成功,进行重试
        if (!$status) {
            if ($returnData[2] < $this->retryCount) {
                //重试次数加1
                list($taskName, $params, $retryNumber, $dstWorkerId) = $returnData;
                $retryNumber = $retryNumber + 1;
                $this->send($taskName, $params, $retryNumber, $dstWorkerId);
            } else {
                $this->log($exception, $returnData);
            }
        }
    }

    private function log($exception, $returnData)
    {
        //超过次数,记录日志
        $msg = date('Y-m-d H:i:s') . " " . json_encode($returnData, JSON_UNESCAPED_UNICODE);
        if ($exception) {
            $msg .= "\n================================================\n" .
                $exception .
                "\n================================================\n";
        }

        Log::error($msg);
    }

    public function __call($name, $arguments)
    {
        if(!$name){
            throw new \Exception("task name is null");
        }

        $server = Context::swlserver();
        if($server){
            $dstWorkerId = $this->getDstWorkerId();
            $this->send($name, $arguments, $this->retryCount,$dstWorkerId);
        }else{
            Log::sysinfo("task run warning: task run use nonBlock mode");
            Timer::after(1,function() use($name, $arguments){
                $this->start([$name, $arguments]);
            });
        }
    }


    protected function send($taskName, $params = [], $retryNumber = 0, $dstWorkerId = -1)
    {
        $server = Context::swlserver();
        $sendData = [$taskName, $params, $retryNumber, $dstWorkerId];
//        var_dump($dstWorkerId."|=============");
//        var_dump($server->task_worker_num."|=============");
        $server->task($sendData, $dstWorkerId);
        //执行数据清空event
        Event::fire("clear");
    }

    /**
     * 获取进程对应关系
     * @return mixed
     */
    protected function getDstWorkerId(){
        if(self::$numbersTmp){
            return array_pop(self::$numbersTmp);
        }else{
            $taskConfig = Config::get("swoole.jobd");
            $taskNumber = $taskConfig["task_worker_num"]-1;
            $start = 0;
            $end = $taskNumber;
            $numbers = range($start, $end);
            //按照顺序执行,保证每个连接池子数固定
            self::$numbersTmp = $numbers;
            return array_pop(self::$numbersTmp);
        }
    }
}