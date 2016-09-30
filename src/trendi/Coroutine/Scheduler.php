<?php
namespace Trendi\Coroutine;

use Trendi\Log\Log;

class Scheduler
{
    protected $maxTaskId = 0;
    protected $taskMap = []; // taskId => task
    protected $taskQueue;
    // resourceID => [socket, tasks]
    protected $waitingForRead = [];
    protected $waitingForWrite = [];

    public function __construct()
    {
        $this->taskQueue = new \SplQueue();
    }

    public function newTask(\Generator $coroutine)
    {
        if($this->maxTaskId >=PHP_INT_MAX){
            $tid = 1;
        }else{
            $tid = ++$this->maxTaskId;
        }
        
        $task = new Task($tid, $coroutine);
        $this->taskMap[$tid] = $task;
        $this->schedule($task);
        return $tid;
    }

    public function schedule(Task $task)
    {
        $this->taskQueue->enqueue($task);
    }

    public function run()
    {

        while (1) {
            $task = $this->taskQueue->dequeue();
            if($this->taskQueue->isEmpty()){
               break;
            }
            dump("taskId:".$task->getTaskId());
            try {
                $retval = $task->run();
                if ($retval instanceof SystemCall) {
                    $retval($task, $this);
                    continue;
                }
            } catch (\Exception $e) {
                $task->setException($e);
                $this->schedule($task);
            } catch (\Error $e) {
                $task->setException($e);
                $this->schedule($task);
            }
            if ($task->isFinished()) {
                unset($this->taskMap[$task->getTaskId()]);
            } else {
                $this->schedule($task);
            }
        }
    }

    public function killTask($tid)
    {
        if (!isset($this->taskMap[$tid])) {
            return false;
        }
        unset($this->taskMap[$tid]);

        // This is a bit ugly and could be optimized so it does not have to walk the queue,
        // but assuming that killing tasks is rather rare I won't bother with it now
        foreach ($this->taskQueue as $i => $task) {
            if ($task->getTaskId() === $tid) {
                unset($this->taskQueue[$i]);
                break;
            }
        }
        return true;
    }

    public function waitForRead($socket, Task $task)
    {
        if (isset($this->waitingForRead[(int)$socket])) {
            $this->waitingForRead[(int)$socket][1][] = $task;
        } else {
            $this->waitingForRead[(int)$socket] = [$socket, [$task]];
        }
    }

    public function waitForWrite($socket, Task $task)
    {
        if (isset($this->waitingForWrite[(int)$socket])) {
            $this->waitingForWrite[(int)$socket][1][] = $task;
        } else {
            $this->waitingForWrite[(int)$socket] = [$socket, [$task]];
        }
    }

    /**
     * @param $timeout 0 represent
     */
    protected function ioPoll($timeout)
    {
        if (!$this->waitingForRead && !$this->waitingForRead) return;

        $rSocks = [];
        foreach ($this->waitingForRead as list($socket)) {
            $rSocks[] = $socket;
        }

        $wSocks = [];
        foreach ($this->waitingForWrite as list($socket)) {
            $wSocks[] = $socket;
        }

        $eSocks = []; // dummp
        //when $timeout is zero, current thread will be blocking
        if (!@stream_select($rSocks, $wSocks, $eSocks, $timeout)) {
            return;
        }

        foreach ($rSocks as $socket) {
            Log::info('socket resource id #' . (int)$socket . ' will be used' . PHP_EOL);
            list(, $tasks) = $this->waitingForRead[(int)$socket];
            unset($this->waitingForRead[(int)$socket]);

            foreach ($tasks as $task) {
                $this->schedule($task);
            }
        }

        foreach ($wSocks as $socket) {
            Log::info('socket resource id #' . (int)$socket . ' will be used' . PHP_EOL);
            list(, $tasks) = $this->waitingForWrite[(int)$socket];
            unset($this->waitingForWrite[(int)$socket]);

            foreach ($tasks as $task) {
                $this->schedule($task);
            }
        }
    }

    /**
     * @return Generator object for newTask
     *
     */
    protected function ioPollTask()
    {
        while (true) {
            if ($this->taskQueue->isEmpty()) {
                $this->ioPoll(null);
            } else {
                $this->ioPoll(0);
            }
            yield;
        }
    }

    /**
     * running tasking with ioPoll
     *
     * @return $this
     */
    public function withIoPoll()
    {
        $this->newTask($this->ioPollTask());
        return $this;
    }
}