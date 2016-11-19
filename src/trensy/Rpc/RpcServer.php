<?php
/**
 * rpc server
 *
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 下午6:51
 */
namespace Trensy\Rpc;

use Trensy\Foundation\Application;
use Trensy\Server\SocketInterface;
use Trensy\Server\SocketServer;
use Trensy\Coroutine\Event;
use Trensy\Support\ElapsedTime;
use Trensy\Rpc\Exception\InvalidArgumentException;
use Trensy\Mvc\Route\RouteMatch;

class RpcServer implements SocketInterface
{
    private $root = null;
    private $config = null;
    private $serialize = null;
    private $server = null;
    private $serverName = null;

    public function __construct($server, $serialize, $config, $root, $serverName = "trensy")
    {
        $this->config = $config;
        $this->root = $root;
        $this->serialize = $serialize;
        $this->server = $server;
        $this->serverName = $serverName;
    }

    public function start()
    {
        $tcpServer = new SocketServer($this->server, $this->config['server'], $this, "rpc", $this->serverName);
        $tcpServer->start();
    }

    public function bootstrap()
    {
        $obj = new Application($this->root);
        $obj->rpcBootstrap();
    }
    
    public function getSerialize()
    {
        return $this->serialize;
    }

    public function perform($data, $serv, $fd, $from_id)
    {
        $result = $this->serialize->xformat($data);
        if (!$result) {
            throw new InvalidArgumentException(" received body parse fail");
        }
        list($url, $params) = $result;
        RouteMatch::getInstance()->runSocket($url, $params, $serv,$fd);
    }
}