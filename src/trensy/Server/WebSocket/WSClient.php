<?php
/**
 * User: Peter Wang
 * Date: 16/12/15
 * Time: 下午10:26
 */

namespace Trensy\Server\WebSocket;


class WSClient extends BaseClient
{

    public function __construct($host = '127.0.0.1', $port = 8080, $path = '/', $origin = null)
    {
        parent::__construct($host, $port, $path, $origin);
    }

    public function get($path, $params=[])
    {
        $requestData = [];
        $requestData[] = $path;
        $requestData[] = $params;
        $jsonStr = json_encode($requestData);
        try{
            $data = $this->connect();
            if(!$data) throw new \Exception("websocket_connect_error:connect fail!");
            $this->send($jsonStr);
            $tmp = $this->recv();
            return $tmp;
        }catch (\Exception $e){
            throw $e;
        }
    }
}