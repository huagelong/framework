<?php
/**
 * User: Peter Wang
 * Date: 16/9/22
 * Time: 下午12:49
 */

namespace Trendi\Foundation\Storage;

use Config;
use Trendi\Foundation\Exception\ConfigNotFoundException;
use Trendi\Foundation\Storage\Adapter\PdoAbstract as PdoAdapter;
use Trendi\Pool\PoolClient;
use Trendi\Coroutine\Event;
use Trendi\Support\Log;

class Pdo extends PdoAdapter
{
    protected static $client = null;
    protected $config = null;
    
    public function __construct()
    {
        $this->config = Config::get("client.pool");
        if (!$this->config) {
            throw new ConfigNotFoundException("client.pool not config");
        }
        $prefix = isset($this->config['pdo']['prefix']) ? $this->config['pdo']['prefix'] : null;
        if (!$prefix) {
            $prefix = Config::get("pdo.prefix");
        }

        $this->prefix = $prefix;
        
        $this->initialize();
        parent::__construct();
    }

    public function initialize()
    {
        if(self::$client) return;
        Log::sysinfo("new redis client conn");
        self::$client = new PoolClient($this->config['host'], $this->config['port'], $this->config['serialization'],$this->config);
    }

    /**
     *
     * 更新，插入，删除sql执行,只返回受影响的行数
     * @param unknown_type $sql
     * @param unknown_type $data
     */
    public function exec($sql, $connType = self::CONN_MASTER)
    {
        if (!$sql) {
            return false;
        }

        if (!(strtolower(substr($sql, 0, 6)) == 'insert' || strtolower(substr($sql, 0, 4)) == 'update'
            || strtolower(substr($sql, 0, 4)) == 'drop' || strtolower(substr($sql, 0, 4)) == 'delete'
            || strtolower(substr($sql, 0, 4)) == 'create')
        ) {
            throw new \Exception("only run on select , show");
        }

        self::$_sql[] = $sql;

        $params = [
            $sql,
            $connType
        ];
        $data = self::$client->get("pdo", $params);
        return $data;
    }


    public function fetchAll($sql, $connType = self::CONN_SLAVE)
    {
        if (empty($sql)) {
            return false;
        }
        self::$_sql[] = $sql;

        $params = [
            $sql,
            $connType,
            "fetchAll"
        ];
        $data = self::$client->get("pdo", $params);
        return $data;
    }


    public function fetch($sql, $connType = self::CONN_SLAVE)
    {
        if (empty($sql)) {
            return false;
        }
        self::$_sql[] = $sql;
        $params = [
            $sql,
            $connType,
            "fetch"
        ];
        $data = self::$client->get("pdo", $params);
        return $data;
    }

    public function __destruct()
    {
        Event::bind("clear", function () {
            self::clearStaticData();
        });
//        self::$client->close();
    }
}