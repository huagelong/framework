<?php
/**
 * User: Peter Wang
 * Date: 16/12/15
 * Time: 下午10:01
 */

namespace Trensy\Server\WebSocket;


use Trensy\Server\HttpServer;
use Trensy\Support\ElapsedTime;
use Trensy\Mvc\Route\RouteMatch;
use swoole_http_server as SwooleServer;
use Trensy\Support\Tool;

class WSServer extends HttpServer
{
    const RESPONSE_SUCCESS_CODE = 200;
    const RESPONSE_NORMAL_ERROR_CODE = 500;
    public static $allFd = [];
    
    public function __construct(SwooleServer $swooleServer, array $config, $adapter, $serverName)
    {
        parent::__construct($swooleServer, $config, $adapter, $serverName);
    }

    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
    {
        //todo 保存到redis
        self::$allFd[$request->fd] = $request->fd;//首次连上时存起来
    }

    public function onWsMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
    {
        if($frame->data){
            list($path, $params) = json_decode($frame->data, true);
            RouteMatch::getInstance()->runSocket($path, $params, $server,$frame->fd);
        }else{
            $content = $this->render('', self::RESPONSE_NORMAL_ERROR_CODE, "收到的数据为空!");
            $server->push($frame->fd, $this->render($content));
        }
    }

    public function onClose(swoole_server $server, $fd)
    {
        unset(self::$allFd[$fd]);
    }

    protected function render($data='', $errorCode = self::RESPONSE_SUCCESS_CODE, $errorMsg = '')
    {
        $elapsedTime = ElapsedTime::runtime("sys_elapsed_time");
        $result = [];
        $result['result'] = $data;
        $result['statusCode'] = $errorCode;
        $result['msg'] = $errorMsg;
        $result['elapsedTime'] = $elapsedTime;
        return Tool::my_json_encode($result);
    }

}