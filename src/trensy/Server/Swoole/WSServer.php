<?php
/**
 * User: Peter Wang
 * Date: 16/12/15
 * Time: 下午10:01
 */

namespace Trensy\Server\Swoole;


use Trensy\Support\ElapsedTime;
use Trensy\Mvc\Route\RouteMatch;
use swoole_http_server as SwooleServer;
use Trensy\Support\Tool;

class WSServer extends HttpdServer
{
    const RESPONSE_SUCCESS_CODE = 200;
    const RESPONSE_NORMAL_ERROR_CODE = 500;
    public static $allFd = [];
    
    public function __construct(\swoole_websocket_server $swooleServer=null)
    {
        parent::__construct($swooleServer);
    }

    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
    {
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

    public function onClose(\swoole_websocket_server $server, $fd)
    {
//        unset(self::$allFd[$fd]);
    }

    protected function render($data='', $errorCode = self::RESPONSE_SUCCESS_CODE, $errorMsg = '')
    {
        $elapsedTime = ElapsedTime::runtime(ElapsedTime::SYS_START);
        $result = [];
        $result['result'] = $data;
        $result['statusCode'] = $errorCode;
        $result['msg'] = $errorMsg;
        $result['elapsedTime'] = $elapsedTime;
        return Tool::myJsonEncode($result);
    }

}