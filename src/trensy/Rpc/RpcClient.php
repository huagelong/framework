<?php
/**
 * rpc client
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Rpc;


use Trensy\Server\SocketClient;
use Trensy\Support\Arr;

class RpcClient
{

    private $client = null;

    public function __construct($host, $port, $serialization = 1, $diyConfig = [])
    {
        $config = [
            "host" => "127.0.0.1",
            "port" => "9000",
            'open_length_check' => 1,
            'package_length_type' => 'N',
            'package_length_offset' => 0,
            'package_body_offset' => 4,
            'package_max_length' => 2000000,
            "serialization" => 1,
            "timeout" => 3,
            "alway_keep" => false,
        ];

        $config = Arr::merge($config, $diyConfig);

        $config['host'] = $host;
        $config['port'] = $port;
        $config['serialization'] = $serialization;
        $serialization = new RpcSerialization($config['serialization'], $config['package_body_offset']);
        $client = new \swoole_client($config['alway_keep'] ? SWOOLE_SOCK_TCP | SWOOLE_KEEP : SWOOLE_TCP);
        $this->client = new SocketClient($client, $config, $serialization);
    }

    /**
     * 获取数据
     * 
     * @param $url
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function get($url, $params = [])
    {
        $result = [$url, $params];
        return $this->client->sendAndRecvice($result);
    }

    public function close()
    {
        $this->client->close();
    }

    public function __destruct()
    {
        $this->close();
    }
}