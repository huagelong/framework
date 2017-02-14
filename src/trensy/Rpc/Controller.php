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

namespace Trensy\Rpc;

use Trensy\Support\ElapsedTime;

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
        $elapsedTime = ElapsedTime::runtime("rpc_sys_elapsed_time");
        $result = [];
        $result['result'] = $data;
        $result['statusCode'] = $errorCode;
        $result['msg'] = $errorMsg;
        $result['elapsedTime'] = $elapsedTime;
        return $result;
    }

    /**
     * @param $data
     * @param int $errorCode
     * @param string $errorMsg
     */
    public function response($data, $errorCode = self::RESPONSE_SUCCESS_CODE, $errorMsg = '')
    {
        $data = $this->render($data, $errorCode, $errorMsg);
        $this->server->send($this->fd, $data);
//        $this->server->close($this->fd);
    }
}