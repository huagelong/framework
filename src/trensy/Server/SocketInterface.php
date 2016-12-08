<?php
/**
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Server;


interface SocketInterface
{
    /**
     *  数据初始化
     *
     * @return mixed
     */
    public function bootstrap();

    /**
     *  receive 执行函数
     *
     * @param $data
     * @param $serv
     * @param $fd
     * @param $from_id
     * @return mixed
     */
    public function perform($data, $serv, $fd, $from_id);

}