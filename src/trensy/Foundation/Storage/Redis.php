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

namespace Trensy\Foundation\Storage;

use Trensy\Config\Config;
use Trensy\Foundation\Exception\ConfigNotFoundException;
use Trensy\Support\Log;
use Predis\Client;
use Trensy\Support\Exception as SupportException;

class Redis
{
    public static $conn = null;
    protected $config = null;

    public function __construct($config=null)
    {
        $this->config = $config;
        $this->conn();
    }

    protected function conn()
    {
        if(!self::$conn){
            if(!$this->config){
                $this->config = Config::get("storage.server.redis");
                if(!$this->config) throw new ConfigNotFoundException("storage.server.redis not config");
            }
            $this->initialize($this->config);
        }
    }

    protected function initialize($config)
    {
        if(self::$conn) return ;
        $servers = $config['servers'];
        $options = $config['options'];
        try {
            self::$conn = new Client($servers, $options);
        } catch (\Exception $e) {
            Log::error(SupportException::formatException($e));
        }catch (\Error $e) {
            Log::error(SupportException::formatException($e));
        }
    }

    public function __call($name, $arguments)
    {
        try {
            $result = $this->run($name ,$arguments);
            return $result;
        } catch (\Exception $e) {
            $this->reConn();
            if(self::$conn){
                return $this->run($name ,$arguments);
            }else{
                Log::error(SupportException::formatException($e));
            }
        }catch (\Error $e) {
            $this->reConn();
            if(self::$conn){
                return $this->run($name ,$arguments);
            }else{
                Log::error(SupportException::formatException($e));
            }
        }
    }

    protected function run($name ,$arguments)
    {
        if ($arguments) {
            $result =  call_user_func_array([self::$conn, $name], $arguments);
//            $result = self::$conn->$name(...$arguments);
        } else {
            $result = self::$conn->$name();
        }
        if($result instanceof \Predis\Response\Status){
            $result =  $result->getPayload();
            $result = $result=='OK'?true:false;
        }
        return $result;
    }

    protected function reConn(){
        self::$conn = [];
        $this->conn();
    }
    
}