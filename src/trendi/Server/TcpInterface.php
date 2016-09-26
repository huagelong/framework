<?php
/**
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 下午6:49
 */

namespace Trendi\Server;


interface TcpInterface
{

    public function bootstrap();

    public function go($data, $serv, $fd, $from_id);

}