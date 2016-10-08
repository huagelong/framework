<?php
/**
 *  session handle
 *
 * User: Peter Wang
 * Date: 16/9/23
 * Time: 下午5:40
 */

namespace Trendi\Foundation\Bootstrap;

use Trendi\Cache\Adapter\RedisCache;
use Trendi\Config\Config;
use Trendi\Support\Log;

class SessionBootstrap
{
    protected static $instance = [];

    /**
     *  instance
     * @return object
     */
    public static function getInstance()
    {
        if (isset(self::$instance) && self::$instance) return self::$instance;

        return self::$instance = new self();
    }


    /**
     * @var \Trendi\Cache\Adapter\RedisCache
     */
    private $server = null;

    public function __construct()
    {
        session_write_close();

        $this->server = new RedisCache();
        session_set_save_handler(
            array($this, "open"),
            array($this, "close"),
            array($this, "read"),
            array($this, "write"),
            array($this, "destroy"),
            array($this, "gc")
        );

        if(session_status() != PHP_SESSION_NONE && (session_status() != PHP_SESSION_ACTIVE)){
            session_start();
        }
    }

    public function open($savePath, $sessionName)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        Log::debug("sessionRead:".$id);
        return $this->server->get($id);
    }

    public function write($id, $data)
    {
        $expire = Config::get("app.session_timeout");
        $expire = $expire ? $expire : 60 * 60;
        $this->server->set($id, $data, $expire);
    }

    public function destroy($id)
    {
        return $this->server->del($id);
    }

    public function gc($maxlifetime)
    {
    }
}