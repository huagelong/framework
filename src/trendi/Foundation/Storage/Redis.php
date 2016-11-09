<?php
/**
 * User: Peter Wang
 * Date: 16/9/22
 * Time: 下午12:49
 */

namespace Trendi\Foundation\Storage;

use Trendi\Config\Config;
use Trendi\Foundation\Exception\ConfigNotFoundException;
use Trendi\Support\Log;
use Predis\Client;
use Trendi\Support\Exception as SupportException;

class Redis
{
    protected static $client = null;

    protected static $conn = null;

    public function __construct()
    {
        $this->initializeDefault();
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

    public function __call($name, $arguments)
    {
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
    }

    public function __destruct()
    {
    }
}