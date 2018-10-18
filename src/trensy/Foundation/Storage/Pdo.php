<?php
/**
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */

namespace Trensy\Foundation\Storage;

use Trensy\Config;
use Trensy\Foundation\Exception\ConfigNotFoundException;
use Trensy\Event;
use Trensy\Foundation\Storage\Adapter\SQlAbstract;
use Trensy\Support\Exception;
use Trensy\Log;

class Pdo extends SQlAbstract
{
    public static $conn = [];
    protected $config = null;
    protected $key = null;

    public function __construct($config=null)
    {
        $this->config = $config;
        if(!$this->config){
            $this->config = Config::get("storage.server.pdo");
            if(!$this->config) throw new ConfigNotFoundException("storage.server.pdo not config");
        }

        $this->key = md5(serialize($this->config));
        $this->conndb();
    }


    public function getConnect($hostKey=self::CONN_MASTER)
    {
        return self::$conn[$this->key][$hostKey];
    }

    protected function conndb()
    {

        if(!isset(self::$conn[$this->key]) || !self::$conn[$this->key]){
            self::$prefix = $this->config['prefix'];
            parent::__construct();
            $this->initConn($this->config);
        }
    }

    protected function initConn($config)
    {
        if(isset(self::$conn[$this->key]) && self::$conn[$this->key]) return self::$conn[$this->key];
        try {

            if (isset($config['master']) && !isset(self::$conn[$this->key][self::CONN_MASTER])) {
                $masterConfig = $config['master'];
                $masterOptions = array(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY=>true,\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',\PDO::ATTR_TIMEOUT=>$masterConfig['timeout'],\PDO::ATTR_PERSISTENT=>true);
                if(php_sapi_name() != 'cli') $masterOptions = array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',\PDO::ATTR_TIMEOUT=>$masterConfig['timeout']);
                $dbh = new \PDO($config['type'] . ':host=' . $masterConfig['host'] . ';port=' . $masterConfig['port'] . ';dbname=' . $masterConfig['db_name'] . '',
                    $masterConfig['user'], $masterConfig['password'],$masterOptions);
                if((php_sapi_name() == 'cli') && strtolower($config['type'])=='mysql'){
                    $query = $dbh->prepare("set session wait_timeout=90000,interactive_timeout=90000,net_read_timeout=90000");
                    $query->execute();
                }
                self::$conn[$this->key][self::CONN_MASTER] = $dbh;
                self::$conn[$this->key][self::CONN_MASTER]->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
                self::$conn[$this->key][self::CONN_MASTER]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }
            if (isset($config['slave']) && !isset(self::$conn[$this->key][self::CONN_MASTER])) {
                $slaveConfig = $config['slave'];
                $slavOptions = array(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY=>true,\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',\PDO::ATTR_TIMEOUT=>$slaveConfig['timeout'],\PDO::ATTR_PERSISTENT=>true);
                if(php_sapi_name() != 'cli') $slavOptions = array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',\PDO::ATTR_TIMEOUT=>$slaveConfig['timeout']);
                    $slaveDBH = new \PDO($config['type'] . ':host=' . $slaveConfig['host'] . ';port=' . $slaveConfig['port'] . ';dbname=' . $slaveConfig['db_name'] . '',
                    $slaveConfig['user'], $slaveConfig['password'],$slavOptions
                    );
                if((php_sapi_name() == 'cli') && strtolower($config['type'])=='mysql'){
                    $query = $slaveDBH->prepare("set session wait_timeout=90000,interactive_timeout=90000,net_read_timeout=90000");
                    $query->execute();
                }
                self::$conn[$this->key][self::CONN_SLAVE] = $slaveDBH;
                self::$conn[$this->key][self::CONN_SLAVE]->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
                self::$conn[$this->key][self::CONN_SLAVE]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }

        } catch (\PDOException $e) {
            Log::error(Exception::formatException($e));
            throw $e;
        }

        if (!isset(self::$conn[$this->key][self::CONN_MASTER])) {
            throw new \PDOException('master database server must set ~');
        }

        if (!isset(self::$conn[$this->key][self::CONN_SLAVE])) {
            self::$conn[$this->key][self::CONN_SLAVE] = self::$conn[$this->key][self::CONN_MASTER];
        }

        return self::$conn[$this->key];
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

        if (!(strtolower(substr($sql, 0, 6)) == 'insert' || strtolower(substr($sql, 0, 6)) == 'update'
            || strtolower(substr($sql, 0, 4)) == 'drop' || strtolower(substr($sql, 0, 6)) == 'delete'
            || strtolower(substr($sql, 0, 6)) == 'create'
            || strtolower(substr($sql, 0, 5)) == 'begin'
            || strtolower(substr($sql, 0, 6)) == 'commit'
            || strtolower(substr($sql, 0, 8)) == 'rollback'
            || strtolower(substr($sql, 0, 3)) == 'set'
            || strtolower(substr($sql, 0, 5)) == 'alter'
        )
        ) {
            throw new \Exception("only run on not select");
        }

        self::$_sql['sql'] = $sql;
        $func = $isInsert?"lastInsertId":"";
        return $this->set($sql, $connType, $func);
    }


    public function fetchAll($sql, $connType = self::CONN_SLAVE)
    {
        if (empty($sql)) {
            return false;
        }

        self::$_sql['sql'] = $sql;
        $func = "fetchAll";
        return $this->set($sql, $connType, $func);
    }


    public function fetch($sql, $connType = self::CONN_SLAVE)
    {
        if (empty($sql)) {
            return false;
        }

        self::$_sql['sql'] = $sql;
        $func = "fetch";
        return $this->set($sql, $connType, $func);
    }

    public function __destruct()
    {
        Event::bind("clear", function () {
            self::clearStaticData();
        });
    }


    protected function set($sql, $connType, $method)
    {
        try{
            $result = [];
            if (!$method || $method == 'lastInsertId') {
                $result = self::$conn[$this->key][$connType]->exec($sql);
                if ($method) {
                    $result = self::$conn[$this->key][$connType]->$method();
                }
                if (self::$conn[$this->key][$connType]->errorCode() != '00000') {
                    $error = self::$conn[$this->key][$connType]->errorInfo();
                    $errorMsg = 'ERROR: [' . $error['1'] . '] ' . $error['2'];
                    throw new \Exception($errorMsg, self::$conn[$this->key][$connType]->errorCode());
                }
                return $result;
            } else {
                $query = self::$conn[$this->key][$connType]->query($sql);
//                dump('1');
//                dump($query);
                if($query === false){
                    throw new \Exception("server has gone away");
                }
                $result = $query->$method();
//                dump('2');
//                dump(self::$conn[$this->key][$connType]->errorCode());
                if (self::$conn[$this->key][$connType]->errorCode() != '00000') {
                    $error = self::$conn[$this->key][$connType]->errorInfo();
                    $errorMsg = 'ERROR: [' . $error['1'] . '] ' . $error['2'];
                    throw new \Exception($errorMsg, self::$conn[$this->key][$connType]->errorCode());
                }
                return $result;
            }
        }catch (\Error $e){
            $errorMsgStr = $e->getMessage();
            if(!$this->checkErrors($errorMsgStr)){
                $this->dump($sql);
                Log::error($e->getMessage());
                throw new \Exception($e->getMessage());
            }

            $this->dump($sql);
            Log::error($e->getMessage());
            //重新连接
            self::$conn[$this->key] = [];
            $this->conndb();
            if(isset(self::$conn[$this->key]) && self::$conn[$this->key]){
                return $this->set($sql, $connType, $method);
            }else{
                $this->dump($sql);
                throw new \Exception($e->getMessage());
            }
        }catch (\Exception $e){
            $errorMsgStr = $e->getMessage();
            if(!$this->checkErrors($errorMsgStr)){
                $this->dump($sql);
                Log::error($e->getMessage());
                throw new \Exception($e->getMessage());
            }

            $this->dump($sql);
            Log::error($e->getMessage());
            //重新连接
            self::$conn[$this->key] = [];
            $this->conndb();
            if(isset(self::$conn[$this->key]) && self::$conn[$this->key]){
                return $this->set($sql, $connType, $method);
            }else{
                $this->dump($sql);
                throw new \Exception($e->getMessage());
            }
        }

    }

    function backup($tables=[])
    {
        $tables = is_array($tables)?$tables:explode(',',$tables);
        if(!$tables) return "";
        $return = "";
        $db = self::$conn[$this->key][self::CONN_MASTER];
        foreach($tables as $table) {

            $stmt = $db->query("DESC $table");
            $tableFields = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            $numColumns = count($tableFields);

            $return .= "DROP TABLE $table;";

            $result2 = $db->query("SHOW CREATE TABLE $table");
            $row2 = $result2->fetch();
            $row2 = array_values($row2);
            $return .= "\n\n" . $row2['1'] . ";\n\n";

            $sql = "SELECT * FROM $table";

            for ($i = 0; $i < $numColumns; $i++) {
                foreach ($db->query($sql) as $row) {
                    $row = array_values($row);
                    $return .= "INSERT INTO `$table` VALUES(";
                    for ($j = 0; $j < $numColumns; $j++) {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = str_replace("\n", "\\n", $row[$j]);
                        if (isset($row[$j])) {
                            $return .= '"' . $row[$j] . '"';
                        } else {
                            $return .= '""';
                        }
                        if ($j < ($numColumns - 1)) {
                            $return .= ',';
                        }
                    }
                    $return .= ");\n";
                }
            }

            $return .= "\n\n\n";
        }
        return $return;
    }

    protected function checkErrors($errorMsgStr)
    {
        $needles = [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
            'Transaction() on null',
        ];

        foreach ($needles as $needle) {
            if (mb_strpos($errorMsgStr, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

}