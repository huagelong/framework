<?php
/**
 *  session handle
 *
 * User: Peter Wang
 * Date: 16/9/23
 * Time: 下午5:40
 */

namespace Trendi\Http;

use Trendi\Support\Log;

class Session
{
    protected static $sid = null;

    /**
     * @var \Trendi\Cache\Adapter\RedisCache
     */
    private $server = null;
    private $config = [];


    public function __construct($config=[], $server=null)
    {
        $this->config = $config;
        $this->server = $server;
    }
    
    public function start($request, $response)
    {
        if (self::$sid) return self::$sid;
        
        if(!$this->config||!$this->server) return ;
        
        $config = $this->config;

        $sid = null;
        $lifetime = 0;

        if (isset($config['cache_expire'])) {
            $expire = $config['cache_expire'];
            $lifetime = time()+$expire * 2;
        } else {
            $expire = 60 * 60;
        }

        $path = !isset($config['path']) ? '/' : $config['path'];
        $domain = !isset($config['domain']) ? '' : $config['domain'];
        $secure = !isset($config['secure']) ? false : $config['secure'];
        $httponly = !isset($config['httponly']) ? true : $config['httponly'];

        $sessionName = empty($config['name']) ? 'TSESSIONID' : $config['name'];

        if ($request->query->get($sessionName)) {
            $sid = $request->query->get($sessionName);
        } elseif ($request->request->get($sessionName)) {
            $sid = $request->request->get($sessionName);
        } elseif ($request->cookies->get($sessionName)) {
            $sid = $request->cookies->get($sessionName);
        } else {
            $sid = sha1($request->headers->get('user-agent') . $request->server->get('remote_addr') . uniqid(posix_getpid(), true));
        }
        $response->rawcookie($sessionName, $sid, $lifetime, $path, $domain, $secure, $httponly);
        self::$sid = $sid;
        $this->set("trendy_heart", 1);
        $this->server->expire($sid, $expire);
    }

    /**
     * 获取session
     *
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->server->hget(self::$sid, $key);
    }

    /**
     * 设置session
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->server->hset(self::$sid, $key, $value);
    }

    /**
     * 获取id
     * @return null
     */
    public function getSid()
    {
        return self::$sid;
    }

    /**
     *  删除key
     * @param $key
     * @return mixed
     */
    public function del($key)
    {
        return $this->server->hdel(self::$sid, $key);
    }

    /**
     * 清空
     * @return mixed
     */
    public function clear()
    {
        return $this->server->del(self::$sid);
    }
}