<?php
/**
 *  session handle
 *
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */

namespace Trensy\Foundation\Bootstrap;

use Trensy\Foundation\Storage\Redis;
use Trensy\Config;
use Trensy\Http\Session as HttpSession;

class Session extends HttpSession
{
    public static function getInstance()
    {
        return new self();
    }

    public function __construct()
    {
        $serverConfig = Config::get("storage.server.redis.servers");

        if($serverConfig){
            $config = Config::get("app.session");
            $server = new Redis();
            parent::__construct($config, $server);
        }
    }

}