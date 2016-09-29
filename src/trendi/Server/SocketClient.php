<?php
/**
 *  socket 客户端
 * 
 * User: Peter Wang
 * Date: 16/9/19
 * Time: 下午2:13
 */

namespace Trendi\Server;

use Trendi\Rpc\Exception\ConnectionException;

class SocketClient
{
    private $client;
    private $serialization = null;
    private $config = null;

    private $connected = false;

    public function __construct($swooleClient, $config = [], $serialization)
    {
        $this->serialization = $serialization;
        $this->client = $swooleClient;
        $this->config = $config;
        if (!$this->client->isConnected()) {
            $this->connect($config);
        }
    }

    public function connect($config)
    {
        $connected = $this->client->connect($config['host'], $config['port'], $config['timeout']);
        if (false == $connected) {
            throw new ConnectionException(socket_strerror($this->client->errCode));
        }
        $this->setConnected();
    }

    public function sendAndRecvice($data)
    {
        $formatData = $this->serialization->format($data);
        if (!$this->client->isConnected()) {
            $this->connect($this->config);
        }
        if ($this->client->send($formatData)) {
            try {
                $recvData = $this->client->recv();
            } catch (\Exception $e) {
                throw new \Exception(socket_strerror($this->client->errCode));
            } catch (\Error $e) {
                throw new \Exception(socket_strerror($this->client->errCode));
            }
            if ($data === false) {
                throw new \Exception(socket_strerror($this->client->errCode));
            }
            $xformatData = $this->serialization->xformat($recvData);
            return $xformatData;
        } else {
            throw new \Exception(socket_strerror($this->client->errCode));
        }
        return $this->serialization->xformat("");
    }

    public function isConnected()
    {
        if (!$this->client->isConnected()) {
            $this->setConnected(false);
        }
    }

    public function setConnected()
    {
        $this->connected = true;
    }

    public function close()
    {
        if (!$this->connected) return true;
        return $this->client->close();
    }
}