<?php
/**
 * rpc controller
 *
 * User: Peter Wang
 * Date: 16/9/19
 * Time: 上午11:09
 */

namespace Trensy\Rpc;

use Trensy\Support\ElapsedTime;

class Controller
{

    const RESPONSE_CODE = 200;
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
     * @param string $errodMsg
     * @return array
     */
    public function render($data, $errorCode = self::RESPONSE_CODE, $errodMsg = '')
    {
        $elapsedTime = ElapsedTime::runtime("rpc_sys_elapsed_time");
        $result = [];
        $result['result'] = $data;
        $result['errorCode'] = $errorCode;
        $result['errodMsg'] = $errodMsg;
        $result['elapsedTime'] = $elapsedTime;
        return $result;
    }

    /**
     * @param $data
     * @param int $errorCode
     * @param string $errodMsg
     */
    public function response($data, $errorCode = self::RESPONSE_CODE, $errodMsg = '')
    {
        $data = $this->render($data, $errorCode, $errodMsg);
        $this->server->send($this->fd, $data);
        $this->server->close($this->fd);
    }
}