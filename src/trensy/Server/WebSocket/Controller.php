<?php
/**
 * rpc controller
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

namespace Trensy\Server\WebSocket;

use Trensy\Support\ElapsedTime;
use Trensy\Support\Tool;

class Controller
{

    const RESPONSE_SUCCESS_CODE = 200;
    const RESPONSE_NORMAL_ERROR_CODE = 500;

    private $server = null;
    private $fd = null;

    public function __construct($server, $fd)
    {
        $this->server = $server;
        $this->fd = $fd;
    }

    /**
     * 数据返回
     * 
     * @param $data
     * @param int $errorCode
     * @param string $errorMsg
     * @return array
     */
    public function render($data, $errorCode = self::RESPONSE_SUCCESS_CODE, $errorMsg = '')
    {
        $elapsedTime = ElapsedTime::runtime("sys_elapsed_time");
        $result = [];
        $result['result'] = $data;
        $result['statusCode'] = $errorCode;
        $result['msg'] = $errorMsg;
        $result['elapsedTime'] = $elapsedTime;
        return Tool::myJsonEncode($result);
    }

    /**
     * 广播
     *
     * @param $data
     * @param int $errorCode
     * @param string $errorMsg
     */
    public function broadcast($data, $errorCode = self::RESPONSE_SUCCESS_CODE, $errorMsg = '')
    {
        $data = $this->render($data, $errorCode, $errorMsg);
        $clients = WSServer::$allFd;
        if($clients){
            foreach ($clients as $v){
                $this->server->push($v, $data);
            }
        }
    }

    /**
     * @param $data
     * @param int $errorCode
     * @param string $errorMsg
     */
    public function response($data, $errorCode = self::RESPONSE_SUCCESS_CODE, $errorMsg = '')
    {
        $data = $this->render($data, $errorCode, $errorMsg);
        $this->server->push($this->fd, $data);
    }

    /**
     * 关闭连接
     */
    public function close()
    {
        $this->server->close($this->fd);
    }
}