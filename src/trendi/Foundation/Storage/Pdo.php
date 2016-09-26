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
use Trendi\Support\Coroutine\Event;

class Pdo extends PdoAdapter
{
    protected $client = null;

    public function __construct()
    {
        $this->initialize();
        parent::__construct();
    }

    public function initialize()
    {
        $config = Config::get("client.pool");
        if (!$config) {
            throw new ConfigNotFoundException("client.pool not config");
        }
        $prefix = isset($config['pdo']['prefix']) ? $config['pdo']['prefix'] : null;
        if (!$prefix) {
            $prefix = Config::get("pdo.prefix");
        }

        $this->prefix = $prefix;

        $this->client = new PoolClient($config['host'], $config['port'], $config['serialization']);
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
        $data = $this->client->get("pdo", $params);
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
        $data = $this->client->get("pdo", $params);
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
        $data = $this->client->get("pdo", $params);
        return $data;
    }

    public function __destruct()
    {
        Event::bind("clear", function () {
            self::clearStaticData();
        });
    }
}