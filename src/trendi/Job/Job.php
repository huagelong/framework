<?php
/**
 * User: Peter Wang
 * Date: 16/9/26
 * Time: 上午9:51
 */

namespace Trendi\Job;

use Trendi\Foundation\Storage\Redis;
use Trendi\Job\Exception\InvalidArgumentException;
use Cron\CronExpression;
use Trendi\Server\Reload;
use Trendi\Support\Exception;
class Job
{

    const JOB_KEY_PRE = "JOB_KEY";

    private $config = [];
    private $name = null;
    /**
     * @var \Trendi\Foundation\Storage\Redis
     */
    private $storage = null;

    public function __construct(array $config, $name="")
    {
        $this->config = $config;
        $this->name = $name;
        $this->storage = new Redis();
    }


    public function start($queueName)
    {
        if(!$this->config) return ;
        while(1){
            $this->run($queueName);
        }
    }
    
    public function run($queueName)
    {
        try{
            if(!isset($this->config['perform'][$queueName])) return ;
            $pv = $this->config['perform'][$queueName];
            $key = self::JOB_KEY_PRE.":".$queueName;
            $now= time();
            $data = $this->storage->zrangebyscore($key, 0, $now);
            //原子操作避免重复处理
            $checkKey = self::JOB_KEY_PRE."CHECK";
            $check = $this->storage->setnx($checkKey,1);
            if(!$check){
                $sleep = $pv['sleep']?$pv['sleep']:1;
                sleep($sleep);
            }
            if($data && is_array($data)){
                foreach ($data as $v){
                    Reload::load($this->name, $this->config['server']['mem_reboot_rate']);
                    list(, $value) = explode("@",$v);
                    $valueArr = unserialize($value);
                    $queueName = isset($valueArr[0])?$valueArr[0]:"";
                    $jobObj = isset($valueArr[1])?$valueArr[1]:"";
                    $schedule = isset($valueArr[3])?$valueArr[3]:"";
                    $tag = isset($valueArr[4])?$valueArr[4]:"";
                    $jobObj->perform();
                    $this->storage->zrem($key, $v);
                    if($schedule){
                        $cron = CronExpression::factory($schedule);
                        $runTime= $cron->getNextRunDate()->format('Y-m-d H:i:s');
                        $this->add($queueName, $jobObj, $runTime, $schedule, $tag);
                    }
                }
            }
            $this->storage->del($checkKey);
            $sleep = $pv['sleep']?$pv['sleep']:1;
            sleep($sleep);
        }catch (\Exception $e){
            echo "Job ERROR : \n". Exception::formatException($e);
        }catch (\Error $e){
            echo "Job ERROR : \n". Exception::formatException($e);
        }
    }


    public function add($queueName, $jobObj, $runTime = "", $schedule = "", $tag = "")
    {
        if(!isset($this->config['perform'][$queueName])) return ;
        $key = self::JOB_KEY_PRE.":".$queueName;

        $config = $this->config['perform'][$queueName];

        if($config['only_one']) {
            $data = $this->storage->zcount($key, 0, -1);
            if ($data) return;
        }

        $value = func_get_args();
        if(!$tag){
            $tag = md5(serialize($value));
        }else{
            if(stristr('@',$tag)){
                throw new InvalidArgumentException("tag can not include '@'");
            }
        }

        if(!$runTime && !$schedule){
            $runTime = time();
        }else{
            if(!$runTime){
                $cron = CronExpression::factory($schedule);
                $runTime= $cron->getNextRunDate()->format('Y-m-d H:i:s');
            }
        }
        $value = [];
        $value[0] = $queueName;
        $value[1] = $jobObj;
        $value[2] = $runTime;
        $value[3] = $schedule;
        $value[4] = $tag;

        $saveVale = $tag."@".serialize($value);

        $this->storage->zadd($key, $runTime, $saveVale);
    }

}