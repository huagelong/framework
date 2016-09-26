<?php
/**
 * User: Peter Wang
 * Date: 16/9/23
 * Time: 下午5:40
 */

namespace Trendi\Foundation\Bootstrap;

use Trendi\Cache\Adapter\RedisCache;
use Trendi\Config\Config;

class SessionBootstrap
{
    /**
     * @var \Trendi\Cache\Adapter\RedisCache
     */
    private $server = null;

    public function __construct()
    {
        $this->server = new RedisCache();

        session_set_save_handler(
            array($this, "open"),
            array($this, "close"),
            array($this, "read"),
            array($this, "write"),
            array($this, "destroy"),
            array($this, "gc")
        );
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