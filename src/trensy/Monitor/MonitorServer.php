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

namespace Trensy\Monitor;

use Trensy\Server\SocketInterface;
use Trensy\Server\SocketServer;

class MonitorServer implements SocketInterface
{
    private $config = null;
    private $serialize = null;
    private $server = null;
    private $serverName = null;
    private $performClass = null;

    public function __construct($server, $serialize, $config, $performClass, $serverName = "trensy-monitor")
    {
        $this->config = $config;
        $this->serialize = $serialize;
        $this->server = $server;
        $this->serverName = $serverName;
        $this->performClass = $performClass;
    }

    public function start()
    {
        $tcpServer = new SocketServer($this->server, $this->config['server'], $this, "monitor", $this->serverName);
        $tcpServer->start();
    }

    public function bootstrap()
    {
        
    }

    public function getSerialize()
    {
        return $this->serialize;
    }
    
    public function perform($data, $serv, $fd, $from_id)
    {
        if($this->serialize){
            $result = $this->serialize->xformat($data);
        }else{
            $result = $data;
        }

        if (!$result) {
            throw new \Exception(" received body parse fail");
        }
        //monitor_receive
        if($this->performClass){
            $obj = new $this->performClass;
            if(!method_exists($obj, "perform")){
                throw new \Exception(" 'perform' method must defined");
            }
            $obj->perform($result);
        }
        $serv->close($fd);
    }
}