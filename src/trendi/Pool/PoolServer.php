<?php
/**
 * User: Peter Wang
 * Date: 16/9/19
 * Time: 下午5:41
 */

namespace Trendi\Pool;

use Trendi\Foundation\Application;
use Trendi\Pool\Exception\InvalidArgumentException;
use Trendi\Server\TcpInterface;
use Trendi\Server\TcpServer;
use Trendi\Support\Coroutine\Event;

class PoolServer implements TcpInterface
{
    private $root = null;
    private $config = null;
    private $serialize = null;
    private $server = null;
    private $serverName = null;
    private $timeOut = 10;
    private $poolWorkrNumber = 0;
    private $numbersTmp = [];

    public function __construct($server, $serialize, $config, $root, $serverName = "trendi")
    {
        $this->config = $config;
        $this->timeOut = isset($config['server']['task_timeout']) ? $config['server']['task_timeout'] : $this->timeOut;
        $this->root = $root;
        $this->serialize = $serialize;
        $this->server = $server;
        $this->serverName = $serverName;
        $poolWorkrNumber = 0;
        $poolWorkrNumberConfig = $config['server']['pool_worker_number'];
        if ($poolWorkrNumberConfig) {
            foreach ($poolWorkrNumberConfig as $v) {
                $poolWorkrNumber += $v;
            }
        }
        $this->poolWorkrNumber = $poolWorkrNumberConfig;
        $this->config['server']['task_worker_num'] = $poolWorkrNumber;
    }

    public function start()
    {
        $tcpServer = new TcpServer($this->server, $this->config['server'], $this, "pool", $this->serverName);
        $tcpServer->start();
    }

    public function bootstrap()
    {
        $obj = new Application($this->root);
        $obj->poolBootstrap();
    }

    public function go($data, $serv, $fd, $from_id)
    {
        $result = $this->serialize->xformat($data);
        if (!$result) {
            $serv->send($fd, "");
            return;
        }
        list($driver, $params) = $result;
        $dstWorkerId = $this->getDstWorkerId($driver);
        $this->sendWait($driver, $params, $serv, $fd, $dstWorkerId);
    }

    protected function getDstWorkerId($taskName)
    {
        $taskName = strtolower($taskName);
        if (!isset($this->poolWorkrNumber[$taskName])) return -1;
        $pre = 0;
        $current = 0;
        foreach ($this->poolWorkrNumber as $k => $v) {
            if ($k == $taskName) {
                $current = $v;
                break;
            }
            $pre = $v;
        }
        $start = $pre;
        $end = $pre + $current - 1;
        $numbers = range($start, $end);
        //按照顺序执行,保证每个连接池子数固定
        if (empty($this->numbersTmp)) {
            $this->numbersTmp = $numbers;
        }
        return array_pop($this->numbersTmp);
    }

    public function sendWait($taskName, $params = [], $serv, $fd, $dstWorkerId = -1)
    {
        if (!$fd) {
            throw new InvalidArgumentException(" receive fd is not get");
        }
        $sendData = [$taskName, $params, 0, $dstWorkerId];
        $result = $serv->taskwait($sendData, $this->timeOut, $dstWorkerId);
        list($status, $returnData, $exception) = $result;
        if ($status !== false) {
            $returnData = $this->serialize->format($returnData);
            $serv->send($fd, $returnData);
        } else {
            $this->log($exception, $returnData);
            $exception = $this->serialize->format($exception);
            $serv->send($fd, $exception);
        }
        Event::fire("clear");
    }

    private function log($exception, $returnData)
    {
        //超过次数,记录日志
        $msg = date('Y-m-d H:i:s') . " " . json_encode($returnData);
        if ($exception) {
            $msg .= "\n================================================\n" .
                $exception .
                "\n================================================\n";
        }
        swoole_async_write($this->config['server']['task_fail_log'], $msg);
    }
}