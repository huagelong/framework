<?php
/**
 * User: Peter Wang
 * Date: 16/9/22
 * Time: 下午12:49
 */

namespace Trensy\Foundation\Storage;

use Config;
use Trensy\Coroutine\Db\MysqlAsync as PMysqlAsync;
use Trensy\Coroutine\Db\DbCoroutine;
use Trensy\Foundation\Storage\Adapter\SQlAbstract as SQlAdapter;

class Mysql extends SQlAdapter
{
    private  static $coroutine = null;

    public function __construct($storageConfig=null)
    {
        if(!self::$coroutine){
            if(!$storageConfig){
                $storageConfig = Config::get("storage.server.pdo");
            }
            self::$prefix = $storageConfig['prefix'];
            parent::__construct();
            $pdoPool = new PMysqlAsync($storageConfig);
            self::$coroutine = new DbCoroutine($pdoPool);
        }

    }

    /**
     *
     * 更新，插入，删除sql执行,只返回受影响的行数
     * @param unknown_type $sql
     * @param unknown_type $data
     */
    public function exec($sql, $connType = self::CONN_MASTER, $isInsert=false)
    {
        if (!$sql) {
            return false;
        }

        if (!(strtolower(substr($sql, 0, 6)) == 'insert' || strtolower(substr($sql, 0, 4)) == 'update'
            || strtolower(substr($sql, 0, 4)) == 'drop' || strtolower(substr($sql, 0, 4)) == 'delete'
            || strtolower(substr($sql, 0, 4)) == 'create'
            || strtolower(substr($sql, 0, 5)) == 'begin'
            || strtolower(substr($sql, 0, 6)) == 'commit'
            || strtolower(substr($sql, 0, 8)) == 'rollback'
        )
        ) {
            throw new \Exception("only run on select , show");
        }

        self::$_sql['sql'] = $sql;
        $func = $isInsert?"lastInsertId":"";
        yield self::$coroutine->set($sql, $connType, $func);
    }


    public function fetchAll($sql, $connType = self::CONN_SLAVE)
    {
        if (empty($sql)) {
            return false;
        }

        self::$_sql['sql'] = $sql;
        $func = "fetchAll";
        yield self::$coroutine->set($sql, $connType, $func);
    }


    public function fetch($sql, $connType = self::CONN_SLAVE)
    {
        if (empty($sql)) {
            return false;
        }

        self::$_sql['sql'] = $sql;
        $func = "fetch";
        yield self::$coroutine->set($sql, $connType, $func);
    }

}