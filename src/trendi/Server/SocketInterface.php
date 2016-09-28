<?php
/**
 * User: Peter Wang
 * Date: 16/9/18
 * Time: 下午6:49
 */

namespace Trendi\Server;


interface SocketInterface
{

    public function bootstrap();

    public function perform($data, $serv, $fd, $from_id);

}