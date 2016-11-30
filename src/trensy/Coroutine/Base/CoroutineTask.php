<?php
/**
 *  初始化
 * User: Peter Wang
 * Date: 16/9/8
 * Time: 下午5:27
 */

namespace Trensy\Coroutine\Base;

use Trensy\Support\Exception;
use Trensy\Support\Log;

class CoroutineTask{
    protected $callbackData;
    protected $stack;
    protected $callData;
    protected $routine;
    protected $exception = null;
    protected $i;

    public function __construct(\Generator $routine)
    {
        $this->routine = $routine;
        $this->stack = new \SplStack();
    }


    /**
     * 协程调度器
     * @param \Generator $routine
     */
    public function work(\Generator $routine){
        while (true) {
            try {
                if(!empty($this->exception)){
                    throw new \Exception($this->exception);
                }
                if (!$routine) {
                    return;
                }
                $value = $routine->current();
                //嵌套的协程
                if ($value instanceof \Generator) {
                    $this->stack->push($routine);
                    $routine = $value;
                    continue;
                }
                //异步IO的父类
                if(is_subclass_of($value, 'Trensy\Coroutine\Base\CoroutineBase')){
                    $this->stack->push($routine);
                    $value->send([$this, 'callback']);
                    return;
                }
                if ($value instanceof \Swoole\Coroutine\RetVal) {
                    // end yeild
                    Log::syslog(__METHOD__ . " yield end words == " . print_r($value, true), __CLASS__);
                    return false;
                }

                if($value===null) {
                    try {
                        $return = $routine->getReturn();
                    }catch(\Exception $e){
                        $return = null;
                    }
                    if(!empty($return)){
                        $this->callbackData = $return;
                    }
                    if (!$this->stack->isEmpty()) {
                        $routine = $this->stack->pop();
                        $routine->send($this->callbackData);
                        continue;
                    } else {
                        if (!$this->routine->valid()) {
                            return;
                        } else {
                            $this->routine->next();
                            continue;
                        }
                    }
                }else{
                    $this->routine->send($value);
                    return false;
                }

            } catch (\Exception $e) {
                while(!$this->stack->isEmpty()) {
                    $routine = $this->stack->pop();
                }
                Log::error(Exception::formatException($e));
                break;
            }
        }
    }
    /**
     * [callback description]
     * @param  [type]   $r        [description]
     * @param  [type]   $key      [description]
     * @param  [type]   $calltime [description]
     * @param  [type]   $res      [description]
     * @return function           [description]
     */
    public function callback($data)
    {
        /*
            继续work的函数实现 ，栈结构得到保存
         */
//        Log::log('callback:'.__METHOD__.print_r($data, true));
        if(!empty($data['exception'])){
            Log::error($data['exception']);
        }else {
            $gen = $this->stack->pop();
            $this->callbackData = $data;
            $value = $gen->send($this->callbackData);
            $this->work($gen);
        }


    }


    /**
     * [isFinished 判断该task是否完成]
     * @return boolean [description]
     */
    public function isFinished()
    {
        return $this->stack->isEmpty() && !$this->routine->valid();
    }

    public function getRoutine()
    {
        return $this->routine;
    }
}