<?php
/**
 * User: Peter Wang
 * Date: 16/9/22
 * Time: 下午12:49
 */

namespace Trensy\Foundation\Storage;

use Trensy\Config\Config;
use Trensy\Foundation\Exception\ConfigNotFoundException;
use Trensy\Support\Log;
use Predis\Client;
use Trensy\Support\Exception as SupportException;

class Redis
{
    protected static $conn = null;

    public function __construct()
    {
        $this->initializeDefault();
    }
    
    protected function initializeDefault()
    {
        if(self::$conn) return ;
        $config = Config::get("storage.server.redis");
        $servers = $config['servers'];
        if(!$servers) throw new ConfigNotFoundException("storage.server.redis.servers not config");
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
            if ($arguments) {
                $result = self::$conn->$name(...$arguments);
            } else {
                $result = self::$conn->$name();
            }
            if($result instanceof \Predis\Response\Status){
                $result =  $result->getPayload();
                $result = $result=='OK'?true:false;
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error(SupportException::formatException($e));
        }catch (\Error $e) {
            Log::error(SupportException::formatException($e));
        }
    }
    
}