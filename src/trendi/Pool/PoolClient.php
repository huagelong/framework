<?php
/**
 * User: Peter Wang
 * Date: 16/9/20
 * Time: 上午9:18
 */

namespace Trendi\Pool;

use Trendi\Server\TcpClient;
use Trendi\Support\Arr;
use Trendi\Support\Serialization\Serialization;

class PoolClient
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

        $serialization = Serialization::get($config['serialization']);
        $serialization->setBodyOffset($config['package_body_offset']);
        $this->client = new TcpClient($config, $serialization);
    }

    public function get($taskname, $params = [])
    {
        $result = [$taskname, $params];
        $data = $this->client->sendAndRecvice($result);
        return $data;
    }


    public function __destruct()
    {
        $this->client->close();
    }
}