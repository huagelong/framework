<?php
/**
 * User: Peter Wang
 * Date: 16/9/22
 * Time: 下午12:49
 */

namespace Kerisy\Foundation\Storage;

use Trendi\Config\Config;
use Trendi\Foundation\Exception\ConfigNotFoundException;
use Trendi\Support\Log;
use Predis\Client;
use Trendi\Server\Pool;
use Trendi\Support\Exception as SupportException;

class Redis
{
    protected static $client = null;
    //适配器类型
    const ADAPTER_DEFAULT = "default";
    const ADAPTER_POOL = "pool";
    private $type = self::ADAPTER_DEFAULT;

    protected static $conn = null;

    public function __construct()
    {
        $type = Config::get("storage.redis.adapter");
        $this->type = $type;
        if($this->type == self::ADAPTER_DEFAULT){
            $this->initializeDefault();
        }else{
            $this->initializePool();
        }
    }

    protected function initializeDefault()
    {
        if(self::$conn) return ;
        $config = Config::get("storage.redis");
        $servers = $config['servers'];
        if(!$servers) throw new ConfigNotFoundException("storage.redis.servers not config");
        $options = $config['options'];
        try {
            self::$conn = new Client($servers, $options);
        } catch (\Exception $e) {
            Log::error(SupportException::formatException($e));
        }catch (\Error $e) {
            Log::error(SupportException::formatException($e));
        }
    }

    protected function initializePool()
    {
        if(self::$client) return ;

        $poolConfig = Config::get("server.pool");
        self::$client = new Pool($poolConfig);
    }

    public function __call($name, $arguments)
    {
        if($this->type == self::ADAPTER_DEFAULT){
            try {
                if ($arguments) {
                    return self::$conn->$name(...$arguments);
                } else {
                    return self::$conn->$name();
                }
            } catch (\Exception $e) {
                Log::error(SupportException::formatException($e));
            }catch (\Error $e) {
                Log::error(SupportException::formatException($e));
            }

        }else{
            $params = [
                $name,
                $arguments
            ];
            $data = self::$client->get("redis", $params);
            return $data;
        }
    }

    public function __destruct()
    {
//        $this->client->close();
    }
}