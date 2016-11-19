<?php
/**
 *  session handle
 *
 * User: Peter Wang
 * Date: 16/9/23
 * Time: 下午5:40
 */

namespace Trensy\Foundation\Bootstrap;

use Trensy\Foundation\Storage\Redis;
use Trensy\Config\Config;
use Trensy\Http\Session as HttpSession;

class Session extends HttpSession
{
    protected static $instance = null;
    
    public static function getInstance()
    {
      if(self::$instance) return self::$instance;
       return self::$instance = new self();
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