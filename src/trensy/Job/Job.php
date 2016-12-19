<?php
/**
 *  job处理
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

namespace Trensy\Job;

use Trensy\Job\Cron\CronExpression;
use Trensy\Foundation\Storage\Redis;
use Trensy\Job\Exception\InvalidArgumentException;
use Trensy\Server\Reload;
use Trensy\Support\Exception;
use Trensy\Support\Log;
use Trensy\Support\Exception\RuntimeExitException;

class Job
{

    const JOB_KEY_PRE = "JOB_KEY";

    private $config = [];

    /**
     * @var \Trensy\Foundation\Storage\Redis
     */
    private $storage = null;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->storage = new Redis();
    }

    /**
     * job 服务开始
     * @param $queueName
     */
    public function start($queueName)
    {
        if (!$this->config) return;

        $timeTick = isset($this->config['server']['timer_tick']) ? $this->config['server']['timer_tick'] : 500;
        $this->config['auto_reload'] = isset($this->config['server']['auto_reload'])?$this->config['server']['auto_reload']:false;
        \swoole_timer_tick($timeTick, function () use ($queueName) {
            $this->run($queueName);
            Reload::perform($this->config['server_name'] . "-master", $this->config['server']['mem_reboot_rate'], $this->config);
        });
    }

    /**
     * job 服务执行
     * @param $queueName
     */
    private function run($queueName)
    {
        try {
            if (!isset($this->config['perform'][$queueName])) return;
            $pv = $this->config['perform'][$queueName];
            $key = self::JOB_KEY_PRE . ":" . $queueName;
            $now = time();
            $data = $this->storage->zrangebyscore($key, 0, $now);
            $initKey ="INIT_".$key;
            $initData = $this->storage->zrangebyscore($initKey, 0, $now);
            if($data && is_array($data)){
                $data = array_merge($data, (array) $initData);
            }else{
                $data = $initData;
            }

            //原子操作避免重复处理
            $checkKey = self::JOB_KEY_PRE . "CHECK";
            $check = $this->storage->setnx($checkKey, 1);
            if (!$check) {
                $sleep = $pv['sleep'] ? $pv['sleep'] : 1;
                sleep($sleep);
            }
            if ($data && is_array($data)) {
                foreach ($data as $v) {
                    $value = $v;
                    $valueArr = unserialize($value);
                    $queueName = isset($valueArr[0]) ? $valueArr[0] : "";
                    $jobObj = isset($valueArr[1]) ? $valueArr[1] : "";
                    $schedule = isset($valueArr[3]) ? $valueArr[3] : "";
                    $isInit = isset($valueArr[4]) ? $valueArr[4] : "";
                    if(!is_object($jobObj)){
                        continue;
                    }
                    $jobObj->perform();
 		    if($isInit){
                        $this->storage->zrem($initKey, $v);
                    }else{
                        $this->storage->zrem($key, $v);
                    }
 
                    if ($schedule) {
                        $cron = CronExpression::factory($schedule);
                        $runTime = $cron->getNextRunDate()->format('Y-m-d H:i:s');
                        $this->add($queueName, $jobObj, $runTime, $schedule, $isInit);
                    }
                }
            }
            $this->storage->del($checkKey);
        } catch (RuntimeExitException $e){
            Log::sysinfo("RuntimeExitException:".$e->getMessage());
        }catch (\Exception $e) {
            Log::error("Job ERROR : \n" . Exception::formatException($e));
        } catch (\Error $e) {
            Log::error("Job ERROR : \n" . Exception::formatException($e));
        }
    }


    /**
     * 添加job
     * @param $queueName
     * @param $jobObj
     * @param string $runTime
     * @param string $schedule
     * @param string $tag
     * @throws InvalidArgumentException
     */
    public function add($queueName, $jobObj, $runTime = "", $schedule = "", $isInit=0)
    {
        if (!isset($this->config['perform'][$queueName])) return;
        $key = self::JOB_KEY_PRE . ":" . $queueName;

        if($isInit) $key = "INIT_".$key;

        $config = $this->config['perform'][$queueName];

        if ($config['only_one']) {
            $data = $this->storage->zrange($key, 0, -1);
//            dump("--------------------job.total-------------------------");
//            dump($data);
            if ($data) return;
        }

        if (!$runTime && !$schedule) {
            $runTime = time();
        } else {
            if (!$runTime) {
                $cron = CronExpression::factory($schedule);
                $runTime = $cron->getNextRunDate()->format('Y-m-d H:i:s');
            }
        }

        $runTime = is_string($runTime) ? strtotime($runTime) : $runTime;

        $value = [];
        $value[0] = $queueName;
        $value[1] = $jobObj;
        $value[2] = $runTime;
        $value[3] = $schedule;
        $value[4] = $isInit;

        $saveVale = serialize($value);

        $this->storage->zadd($key, $runTime, $saveVale);
    }

}