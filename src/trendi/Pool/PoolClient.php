<?php
/**
 * 连接池客户端
 * User: Peter Wang
 * Date: 16/9/20
 * Time: 上午9:18
 */

namespace Trendi\Pool;

use Trendi\Server\SocketClient;
use Trendi\Support\Arr;
use Trendi\Support\Serialization\Serialization;

class PoolClient
{
    private $client = null;

    /**
     * 初始化
     *
     * @param $host
     * @param $port
     * @param int $serialization
     * @param array $diyConfig
     */
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

        $serialization = Serialization::get($config['serialization']);
        $serialization->setBodyOffset($config['package_body_offset']);
        $client = new \swoole_client($config['alway_keep'] ? SWOOLE_SOCK_TCP | SWOOLE_KEEP : SWOOLE_TCP);
        $this->client = new SocketClient($client, $config, $serialization);
    }

    /**
     *  获取数据
     * 
     * @param $taskname
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function get($taskname, $params = [])
    {
        $result = [$taskname, $params];
        $data = $this->client->sendAndRecvice($result);
        return $data;
    }

    public function close()
    {
        $this->client->close();
    }

    public function __destruct()
    {

    }
}