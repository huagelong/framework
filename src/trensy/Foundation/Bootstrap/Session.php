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

use Trensy\Di;
use Trensy\Foundation\Storage\Redis;
use Trensy\Config;
use Trensy\Http\Session as HttpSession;
use Trensy\Foundation\Exception\InvalidArgumentException;

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
            $initConfig = Config::get("app.session_init");
            if ($initConfig) {
                $obj = Di::get($initConfig);
                if (!method_exists($obj, "perform")) {
                    throw new InvalidArgumentException(" log class perform not config ");
                }
                call_user_func_array([$obj, "perform"], []);
            }
            $config = Config::get("app.session");
//            debug($config);
            $server = new Redis();
            parent::__construct($config, $server);
        }
    }

}