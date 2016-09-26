<?php
define('SWOOLE_BASE',4);
define('SWOOLE_THREAD',2);
define('SWOOLE_PROCESS',3);
define('SWOOLE_IPC_UNSOCK',1);
define('SWOOLE_IPC_MSGQUEUE',2);
define('SWOOLE_IPC_CHANNEL',3);
define('SWOOLE_SOCK_TCP',1);
define('SWOOLE_SOCK_TCP6',3);
define('SWOOLE_SOCK_UDP',2);
define('SWOOLE_SOCK_UDP6',4);
define('SWOOLE_SOCK_UNIX_DGRAM',5);
define('SWOOLE_SOCK_UNIX_STREAM',6);
define('SWOOLE_TCP',1);
define('SWOOLE_TCP6',3);
define('SWOOLE_UDP',2);
define('SWOOLE_UDP6',4);
define('SWOOLE_UNIX_DGRAM',5);
define('SWOOLE_UNIX_STREAM',6);
define('SWOOLE_SOCK_SYNC',0);
define('SWOOLE_SOCK_ASYNC',1);
define('SWOOLE_SYNC',2048);
define('SWOOLE_ASYNC',1024);
define('SWOOLE_KEEP',4096);
define('SWOOLE_EVENT_READ',512);
define('SWOOLE_EVENT_WRITE',1024);
define('SWOOLE_VERSION','1.8.11');
define('SWOOLE_AIO_BASE',0);
define('SWOOLE_AIO_LINUX',1);
define('SWOOLE_FILELOCK',2);
define('SWOOLE_MUTEX',3);
define('SWOOLE_SEM',4);
define('SWOOLE_RWLOCK',1);
define('SWOOLE_SPINLOCK',5);
define('WEBSOCKET_OPCODE_TEXT',1);
define('WEBSOCKET_OPCODE_BINARY',2);
define('WEBSOCKET_STATUS_CONNECTION',1);
define('WEBSOCKET_STATUS_HANDSHAKE',2);
define('WEBSOCKET_STATUS_FRAME',3);
define('WEBSOCKET_STATUS_ACTIVE',3);
function swoole_version(){}

function swoole_cpu_num(){}

/**
* @param $fd[required]
* @param $cb[required]
*/
function swoole_event_add($fd,$cb){}

function swoole_event_set(){}

/**
* @param $fd[required]
*/
function swoole_event_del($fd){}

function swoole_event_exit(){}

function swoole_event_wait(){}

/**
* @param $fd[required]
* @param $data[required]
*/
function swoole_event_write($fd,$data){}

/**
* @param $callback[required]
*/
function swoole_event_defer($callback){}

/**
* @param $ms[required]
* @param $callback[required]
* @param $param[optional]
*/
function swoole_timer_after($ms,$callback,$param=null){}

/**
* @param $ms[required]
* @param $callback[required]
*/
function swoole_timer_tick($ms,$callback){}

/**
* @param $timer_id[required]
*/
function swoole_timer_exists($timer_id){}

/**
* @param $timer_id[required]
*/
function swoole_timer_clear($timer_id){}

/**
* @param $settings[required]
*/
function swoole_async_set($settings){}

/**
* @param $filename[required]
* @param $callback[required]
* @param $chunk_size[optional]
* @param $offset[optional]
*/
function swoole_async_read($filename,$callback,$chunk_size=null,$offset=null){}

/**
* @param $filename[required]
* @param $content[required]
* @param $offset[optional]
* @param $callback[optional]
*/
function swoole_async_write($filename,$content,$offset=null,$callback=null){}

/**
* @param $filename[required]
* @param $callback[required]
*/
function swoole_async_readfile($filename,$callback){}

/**
* @param $filename[required]
* @param $content[required]
* @param $callback[optional]
*/
function swoole_async_writefile($filename,$content,$callback=null){}

/**
* @param $domain_name[required]
* @param $content[required]
*/
function swoole_async_dns_lookup($domain_name,$content){}

/**
* @param $read_array[required]
* @param $write_array[required]
* @param $error_array[required]
* @param $timeout[optional]
*/
function swoole_client_select($read_array,$write_array,$error_array,$timeout=null){}

/**
* @param $read_array[required]
* @param $write_array[required]
* @param $error_array[required]
* @param $timeout[optional]
*/
function swoole_select($read_array,$write_array,$error_array,$timeout=null){}

/**
* @param $process_name[required]
*/
function swoole_set_process_name($process_name){}

function swoole_get_local_ip(){}

/**
* @param $errno[required]
*/
function swoole_strerror($errno){}

function swoole_errno(){}

function swoole_load_module(){}

/**
*@since 1.8.11
*/
class swoole_server{
    /**
    * @param $host[required]
    * @param $port[required]
    * @param $mode[optional]
    * @param $sock_type[optional]
    */
    public function __construct($host,$port,$mode=null,$sock_type=null){}

    /**
    * @param $host[required]
    * @param $port[required]
    * @param $sock_type[required]
    */
    public function listen($host,$port,$sock_type){}

    /**
    * @param $host[required]
    * @param $port[required]
    * @param $sock_type[required]
    */
    public function addlistener($host,$port,$sock_type){}

    /**
    * @param $name[required]
    * @param $cb[required]
    */
    public function on($name,$cb){}

    /**
    * @param $zset[required]
    */
    public function set($zset){}

    public function start(){}

    /**
    * @param $fd[required]
    * @param $send_data[required]
    * @param $from_id[optional]
    */
    public function send($fd,$send_data,$from_id=null){}

    /**
    * @param $ip[required]
    * @param $port[required]
    * @param $send_data[optional]
    */
    public function sendto($ip,$port,$send_data=null){}

    /**
    * @param $conn_fd[required]
    * @param $send_data[required]
    */
    public function sendwait($conn_fd,$send_data){}

    /**
    * @param $fd[required]
    */
    public function exist($fd){}

    /**
    * @param $fd[required]
    * @param $is_protected[optional]
    */
    public function protect($fd,$is_protected=null){}

    /**
    * @param $conn_fd[required]
    * @param $filename[required]
    */
    public function sendfile($conn_fd,$filename){}

    /**
    * @param $fd[required]
    */
    public function close($fd){}

    /**
    * @param $fd[required]
    */
    public function confirm($fd){}

    /**
    * @param $fd[required]
    */
    public function pause($fd){}

    /**
    * @param $fd[required]
    */
    public function resume($fd){}

    /**
    * @param $data[required]
    * @param $worker_id[required]
    */
    public function task($data,$worker_id){}

    /**
    * @param $data[required]
    * @param $timeout[optional]
    * @param $worker_id[optional]
    */
    public function taskwait($data,$timeout=null,$worker_id=null){}

    /**
    * @param $tasks[required]
    * @param $timeout[optional]
    */
    public function taskWaitMulti($tasks,$timeout=null){}

    /**
    * @param $data[required]
    */
    public function finish($data){}

    public function reload(){}

    public function shutdown(){}

    public function stop(){}

    public function getLastError(){}

    /**
    * @param $from_id[required]
    */
    public function heartbeat($from_id){}

    /**
    * @param $fd[required]
    * @param $from_id[required]
    */
    public function connection_info($fd,$from_id){}

    /**
    * @param $start_fd[required]
    * @param $find_count[required]
    */
    public function connection_list($start_fd,$find_count){}

    /**
    * @param $fd[required]
    * @param $from_id[required]
    */
    public function getClientInfo($fd,$from_id){}

    /**
    * @param $start_fd[required]
    * @param $find_count[required]
    */
    public function getClientList($start_fd,$find_count){}

    /**
    * @param $ms[required]
    * @param $callback[required]
    * @param $param[optional]
    */
    public function after($ms,$callback,$param=null){}

    /**
    * @param $ms[required]
    * @param $callback[required]
    */
    public function tick($ms,$callback){}

    /**
    * @param $timer_id[required]
    */
    public function clearTimer($timer_id){}

    /**
    * @param $callback[required]
    */
    public function defer($callback){}

    public function sendMessage(){}

    public function addProcess(){}

    public function stats(){}

    /**
    * @param $fd[required]
    * @param $uid[required]
    */
    public function bind($fd,$uid){}


}
/**
*@since 1.8.11
*/
class swoole\server{
    /**
    * @param $host[required]
    * @param $port[required]
    * @param $mode[optional]
    * @param $sock_type[optional]
    */
    public function __construct($host,$port,$mode=null,$sock_type=null){}

    /**
    * @param $host[required]
    * @param $port[required]
    * @param $sock_type[required]
    */
    public function listen($host,$port,$sock_type){}

    /**
    * @param $host[required]
    * @param $port[required]
    * @param $sock_type[required]
    */
    public function addlistener($host,$port,$sock_type){}

    /**
    * @param $name[required]
    * @param $cb[required]
    */
    public function on($name,$cb){}

    /**
    * @param $zset[required]
    */
    public function set($zset){}

    public function start(){}

    /**
    * @param $fd[required]
    * @param $send_data[required]
    * @param $from_id[optional]
    */
    public function send($fd,$send_data,$from_id=null){}

    /**
    * @param $ip[required]
    * @param $port[required]
    * @param $send_data[optional]
    */
    public function sendto($ip,$port,$send_data=null){}

    /**
    * @param $conn_fd[required]
    * @param $send_data[required]
    */
    public function sendwait($conn_fd,$send_data){}

    /**
    * @param $fd[required]
    */
    public function exist($fd){}

    /**
    * @param $fd[required]
    * @param $is_protected[optional]
    */
    public function protect($fd,$is_protected=null){}

    /**
    * @param $conn_fd[required]
    * @param $filename[required]
    */
    public function sendfile($conn_fd,$filename){}

    /**
    * @param $fd[required]
    */
    public function close($fd){}

    /**
    * @param $fd[required]
    */
    public function confirm($fd){}

    /**
    * @param $fd[required]
    */
    public function pause($fd){}

    /**
    * @param $fd[required]
    */
    public function resume($fd){}

    /**
    * @param $data[required]
    * @param $worker_id[required]
    */
    public function task($data,$worker_id){}

    /**
    * @param $data[required]
    * @param $timeout[optional]
    * @param $worker_id[optional]
    */
    public function taskwait($data,$timeout=null,$worker_id=null){}

    /**
    * @param $tasks[required]
    * @param $timeout[optional]
    */
    public function taskWaitMulti($tasks,$timeout=null){}

    /**
    * @param $data[required]
    */
    public function finish($data){}

    public function reload(){}

    public function shutdown(){}

    public function stop(){}

    public function getLastError(){}

    /**
    * @param $from_id[required]
    */
    public function heartbeat($from_id){}

    /**
    * @param $fd[required]
    * @param $from_id[required]
    */
    public function connection_info($fd,$from_id){}

    /**
    * @param $start_fd[required]
    * @param $find_count[required]
    */
    public function connection_list($start_fd,$find_count){}

    /**
    * @param $fd[required]
    * @param $from_id[required]
    */
    public function getClientInfo($fd,$from_id){}

    /**
    * @param $start_fd[required]
    * @param $find_count[required]
    */
    public function getClientList($start_fd,$find_count){}

    /**
    * @param $ms[required]
    * @param $callback[required]
    * @param $param[optional]
    */
    public function after($ms,$callback,$param=null){}

    /**
    * @param $ms[required]
    * @param $callback[required]
    */
    public function tick($ms,$callback){}

    /**
    * @param $timer_id[required]
    */
    public function clearTimer($timer_id){}

    /**
    * @param $callback[required]
    */
    public function defer($callback){}

    public function sendMessage(){}

    public function addProcess(){}

    public function stats(){}

    /**
    * @param $fd[required]
    * @param $uid[required]
    */
    public function bind($fd,$uid){}


}
/**
*@since 1.8.11
*/
class swoole_timer{
    /**
    * @param $ms[required]
    * @param $callback[required]
    * @param $param[optional]
    */
    public static function tick($ms,$callback,$param=null){}

    /**
    * @param $ms[required]
    * @param $callback[required]
    */
    public static function after($ms,$callback){}

    /**
    * @param $timer_id[required]
    */
    public static function exists($timer_id){}

    /**
    * @param $timer_id[required]
    */
    public static function clear($timer_id){}


}
/**
*@since 1.8.11
*/
class swoole\timer{
    /**
    * @param $ms[required]
    * @param $callback[required]
    * @param $param[optional]
    */
    public static function tick($ms,$callback,$param=null){}

    /**
    * @param $ms[required]
    * @param $callback[required]
    */
    public static function after($ms,$callback){}

    /**
    * @param $timer_id[required]
    */
    public static function exists($timer_id){}

    /**
    * @param $timer_id[required]
    */
    public static function clear($timer_id){}


}
/**
*@since 1.8.11
*/
class swoole_event{
    /**
    * @param $fd[required]
    * @param $cb[required]
    */
    public static function add($fd,$cb){}

    /**
    * @param $fd[required]
    */
    public static function del($fd){}

    public static function set(){}

    public static function exit(){}

    /**
    * @param $fd[required]
    * @param $data[required]
    */
    public static function write($fd,$data){}

    public static function wait(){}

    /**
    * @param $callback[required]
    */
    public static function defer($callback){}


}
/**
*@since 1.8.11
*/
class swoole\event{
    /**
    * @param $fd[required]
    * @param $cb[required]
    */
    public static function add($fd,$cb){}

    /**
    * @param $fd[required]
    */
    public static function del($fd){}

    public static function set(){}

    public static function exit(){}

    /**
    * @param $fd[required]
    * @param $data[required]
    */
    public static function write($fd,$data){}

    public static function wait(){}

    /**
    * @param $callback[required]
    */
    public static function defer($callback){}


}
/**
*@since 1.8.11
*/
class swoole_connection_iterator{
    public function rewind(){}

    public function next(){}

    public function current(){}

    public function key(){}

    public function valid(){}

    public function count(){}


}
/**
*@since 1.8.11
*/
class swoole\connectioniterator{
    public function rewind(){}

    public function next(){}

    public function current(){}

    public function key(){}

    public function valid(){}

    public function count(){}


}
/**
*@since 1.8.11
*/
class swoole_server_port{
    private function __construct(){}

    public function __destruct(){}

    public function set(){}

    public function on(){}


}
/**
*@since 1.8.11
*/
class swoole\server\port{
    private function __construct(){}

    public function __destruct(){}

    public function set(){}

    public function on(){}


}
/**
*@since 1.8.11
*/
class swoole_client{
    public function __construct(){}

    public function __destruct(){}

    public function set(){}

    public function connect(){}

    public function recv(){}

    public function send(){}

    public function sendfile(){}

    public function sendto(){}

    public function sleep(){}

    public function wakeup(){}

    public function pause(){}

    public function resume(){}

    public function isConnected(){}

    public function getsockname(){}

    public function getpeername(){}

    public function close(){}

    public function on(){}


}
/**
*@since 1.8.11
*/
class swoole\client{
    public function __construct(){}

    public function __destruct(){}

    public function set(){}

    public function connect(){}

    public function recv(){}

    public function send(){}

    public function sendfile(){}

    public function sendto(){}

    public function sleep(){}

    public function wakeup(){}

    public function pause(){}

    public function resume(){}

    public function isConnected(){}

    public function getsockname(){}

    public function getpeername(){}

    public function close(){}

    public function on(){}


}
/**
*@since 1.8.11
*/
class swoole_http_client{
    public function __construct(){}

    public function __destruct(){}

    public function set(){}

    public function setMethod(){}

    public function setHeaders(){}

    public function setCookies(){}

    public function setData(){}

    public function addFile(){}

    public function execute(){}

    public function push(){}

    public function get(){}

    public function post(){}

    public function upgrade(){}

    public function isConnected(){}

    public function close(){}

    public function on(){}


}
/**
*@since 1.8.11
*/
class swoole\http\client{
    public function __construct(){}

    public function __destruct(){}

    public function set(){}

    public function setMethod(){}

    public function setHeaders(){}

    public function setCookies(){}

    public function setData(){}

    public function addFile(){}

    public function execute(){}

    public function push(){}

    public function get(){}

    public function post(){}

    public function upgrade(){}

    public function isConnected(){}

    public function close(){}

    public function on(){}


}
/**
*@since 1.8.11
*/
class swoole_process{
    public function __construct(){}

    public function __destruct(){}

    public static function wait(){}

    public static function signal(){}

    public static function kill(){}

    public static function daemon(){}

    public static function setaffinity(){}

    public function useQueue(){}

    public function statQueue(){}

    public function freeQueue(){}

    public function start(){}

    public function write(){}

    public function close(){}

    public function read(){}

    public function push(){}

    public function pop(){}

    public function exit(){}

    public function exec(){}

    public function name(){}


}
/**
*@since 1.8.11
*/
class swoole\process{
    public function __construct(){}

    public function __destruct(){}

    public static function wait(){}

    public static function signal(){}

    public static function kill(){}

    public static function daemon(){}

    public static function setaffinity(){}

    public function useQueue(){}

    public function statQueue(){}

    public function freeQueue(){}

    public function start(){}

    public function write(){}

    public function close(){}

    public function read(){}

    public function push(){}

    public function pop(){}

    public function exit(){}

    public function exec(){}

    public function name(){}


}
/**
*@since 1.8.11
*/
class swoole_table{
    /**
    * @param $table_size[required]
    */
    public function __construct($table_size){}

    /**
    * @param $name[required]
    * @param $type[optional]
    * @param $size[optional]
    */
    public function column($name,$type=null,$size=null){}

    public function create(){}

    public function destroy(){}

    /**
    * @param $key[required]
    * @param $value[required]
    */
    public function set($key,$value){}

    /**
    * @param $key[required]
    */
    public function get($key){}

    public function count(){}

    /**
    * @param $key[required]
    */
    public function del($key){}

    /**
    * @param $key[required]
    */
    public function exist($key){}

    /**
    * @param $key[required]
    * @param $column[required]
    * @param $incrby[optional]
    */
    public function incr($key,$column,$incrby=null){}

    /**
    * @param $key[required]
    * @param $column[required]
    * @param $decrby[optional]
    */
    public function decr($key,$column,$decrby=null){}

    public function rewind(){}

    public function next(){}

    public function current(){}

    public function key(){}

    public function valid(){}


}
/**
*@since 1.8.11
*/
class swoole\table{
    /**
    * @param $table_size[required]
    */
    public function __construct($table_size){}

    /**
    * @param $name[required]
    * @param $type[optional]
    * @param $size[optional]
    */
    public function column($name,$type=null,$size=null){}

    public function create(){}

    public function destroy(){}

    /**
    * @param $key[required]
    * @param $value[required]
    */
    public function set($key,$value){}

    /**
    * @param $key[required]
    */
    public function get($key){}

    public function count(){}

    /**
    * @param $key[required]
    */
    public function del($key){}

    /**
    * @param $key[required]
    */
    public function exist($key){}

    /**
    * @param $key[required]
    * @param $column[required]
    * @param $incrby[optional]
    */
    public function incr($key,$column,$incrby=null){}

    /**
    * @param $key[required]
    * @param $column[required]
    * @param $decrby[optional]
    */
    public function decr($key,$column,$decrby=null){}

    public function rewind(){}

    public function next(){}

    public function current(){}

    public function key(){}

    public function valid(){}


}
/**
*@since 1.8.11
*/
class swoole_lock{
    public function __construct(){}

    public function __destruct(){}

    public function lock(){}

    public function trylock(){}

    public function lock_read(){}

    public function trylock_read(){}

    public function unlock(){}


}
/**
*@since 1.8.11
*/
class swoole\lock{
    public function __construct(){}

    public function __destruct(){}

    public function lock(){}

    public function trylock(){}

    public function lock_read(){}

    public function trylock_read(){}

    public function unlock(){}


}
/**
*@since 1.8.11
*/
class swoole_atomic{
    public function __construct(){}

    public function add(){}

    public function sub(){}

    public function get(){}

    public function set(){}

    public function cmpset(){}


}
/**
*@since 1.8.11
*/
class swoole\atomic{
    public function __construct(){}

    public function add(){}

    public function sub(){}

    public function get(){}

    public function set(){}

    public function cmpset(){}


}
/**
*@since 1.8.11
*/
class swoole_http_server extends swoole_server{
    /**
    * @param $ha_name[required]
    * @param $cb[required]
    */
    public function on($ha_name,$cb){}

    public function start(){}

    /**
    * @param $host[required]
    * @param $port[required]
    * @param $mode[optional]
    * @param $sock_type[optional]
    */
    public function __construct($host,$port,$mode=null,$sock_type=null){}

    /**
    * @param $host[required]
    * @param $port[required]
    * @param $sock_type[required]
    */
    public function listen($host,$port,$sock_type){}

    /**
    * @param $host[required]
    * @param $port[required]
    * @param $sock_type[required]
    */
    public function addlistener($host,$port,$sock_type){}

    /**
    * @param $zset[required]
    */
    public function set($zset){}

    /**
    * @param $fd[required]
    * @param $send_data[required]
    * @param $from_id[optional]
    */
    public function send($fd,$send_data,$from_id=null){}

    /**
    * @param $ip[required]
    * @param $port[required]
    * @param $send_data[optional]
    */
    public function sendto($ip,$port,$send_data=null){}

    /**
    * @param $conn_fd[required]
    * @param $send_data[required]
    */
    public function sendwait($conn_fd,$send_data){}

    /**
    * @param $fd[required]
    */
    public function exist($fd){}

    /**
    * @param $fd[required]
    * @param $is_protected[optional]
    */
    public function protect($fd,$is_protected=null){}

    /**
    * @param $conn_fd[required]
    * @param $filename[required]
    */
    public function sendfile($conn_fd,$filename){}

    /**
    * @param $fd[required]
    */
    public function close($fd){}

    /**
    * @param $fd[required]
    */
    public function confirm($fd){}

    /**
    * @param $fd[required]
    */
    public function pause($fd){}

    /**
    * @param $fd[required]
    */
    public function resume($fd){}

    /**
    * @param $data[required]
    * @param $worker_id[required]
    */
    public function task($data,$worker_id){}

    /**
    * @param $data[required]
    * @param $timeout[optional]
    * @param $worker_id[optional]
    */
    public function taskwait($data,$timeout=null,$worker_id=null){}

    /**
    * @param $tasks[required]
    * @param $timeout[optional]
    */
    public function taskWaitMulti($tasks,$timeout=null){}

    /**
    * @param $data[required]
    */
    public function finish($data){}

    public function reload(){}

    public function shutdown(){}

    public function stop(){}

    public function getLastError(){}

    /**
    * @param $from_id[required]
    */
    public function heartbeat($from_id){}

    /**
    * @param $fd[required]
    * @param $from_id[required]
    */
    public function connection_info($fd,$from_id){}

    /**
    * @param $start_fd[required]
    * @param $find_count[required]
    */
    public function connection_list($start_fd,$find_count){}

    /**
    * @param $fd[required]
    * @param $from_id[required]
    */
    public function getClientInfo($fd,$from_id){}

    /**
    * @param $start_fd[required]
    * @param $find_count[required]
    */
    public function getClientList($start_fd,$find_count){}

    /**
    * @param $ms[required]
    * @param $callback[required]
    * @param $param[optional]
    */
    public function after($ms,$callback,$param=null){}

    /**
    * @param $ms[required]
    * @param $callback[required]
    */
    public function tick($ms,$callback){}

    /**
    * @param $timer_id[required]
    */
    public function clearTimer($timer_id){}

    /**
    * @param $callback[required]
    */
    public function defer($callback){}

    public function sendMessage(){}

    public function addProcess(){}

    public function stats(){}

    /**
    * @param $fd[required]
    * @param $uid[required]
    */
    public function bind($fd,$uid){}


}
/**
*@since 1.8.11
*/
class swoole\http\server extends swoole_server{
    /**
    * @param $ha_name[required]
    * @param $cb[required]
    */
    public function on($ha_name,$cb){}

    public function start(){}

    /**
    * @param $host[required]
    * @param $port[required]
    * @param $mode[optional]
    * @param $sock_type[optional]
    */
    public function __construct($host,$port,$mode=null,$sock_type=null){}

    /**
    * @param $host[required]
    * @param $port[required]
    * @param $sock_type[required]
    */
    public function listen($host,$port,$sock_type){}

    /**
    * @param $host[required]
    * @param $port[required]
    * @param $sock_type[required]
    */
    public function addlistener($host,$port,$sock_type){}

    /**
    * @param $zset[required]
    */
    public function set($zset){}

    /**
    * @param $fd[required]
    * @param $send_data[required]
    * @param $from_id[optional]
    */
    public function send($fd,$send_data,$from_id=null){}

    /**
    * @param $ip[required]
    * @param $port[required]
    * @param $send_data[optional]
    */
    public function sendto($ip,$port,$send_data=null){}

    /**
    * @param $conn_fd[required]
    * @param $send_data[required]
    */
    public function sendwait($conn_fd,$send_data){}

    /**
    * @param $fd[required]
    */
    public function exist($fd){}

    /**
    * @param $fd[required]
    * @param $is_protected[optional]
    */
    public function protect($fd,$is_protected=null){}

    /**
    * @param $conn_fd[required]
    * @param $filename[required]
    */
    public function sendfile($conn_fd,$filename){}

    /**
    * @param $fd[required]
    */
    public function close($fd){}

    /**
    * @param $fd[required]
    */
    public function confirm($fd){}

    /**
    * @param $fd[required]
    */
    public function pause($fd){}

    /**
    * @param $fd[required]
    */
    public function resume($fd){}

    /**
    * @param $data[required]
    * @param $worker_id[required]
    */
    public function task($data,$worker_id){}

    /**
    * @param $data[required]
    * @param $timeout[optional]
    * @param $worker_id[optional]
    */
    public function taskwait($data,$timeout=null,$worker_id=null){}

    /**
    * @param $tasks[required]
    * @param $timeout[optional]
    */
    public function taskWaitMulti($tasks,$timeout=null){}

    /**
    * @param $data[required]
    */
    public function finish($data){}

    public function reload(){}

    public function shutdown(){}

    public function stop(){}

    public function getLastError(){}

    /**
    * @param $from_id[required]
    */
    public function heartbeat($from_id){}

    /**
    * @param $fd[required]
    * @param $from_id[required]
    */
    public function connection_info($fd,$from_id){}

    /**
    * @param $start_fd[required]
    * @param $find_count[required]
    */
    public function connection_list($start_fd,$find_count){}

    /**
    * @param $fd[required]
    * @param $from_id[required]
    */
    public function getClientInfo($fd,$from_id){}

    /**
    * @param $start_fd[required]
    * @param $find_count[required]
    */
    public function getClientList($start_fd,$find_count){}

    /**
    * @param $ms[required]
    * @param $callback[required]
    * @param $param[optional]
    */
    public function after($ms,$callback,$param=null){}

    /**
    * @param $ms[required]
    * @param $callback[required]
    */
    public function tick($ms,$callback){}

    /**
    * @param $timer_id[required]
    */
    public function clearTimer($timer_id){}

    /**
    * @param $callback[required]
    */
    public function defer($callback){}

    public function sendMessage(){}

    public function addProcess(){}

    public function stats(){}

    /**
    * @param $fd[required]
    * @param $uid[required]
    */
    public function bind($fd,$uid){}


}
/**
*@since 1.8.11
*/
class swoole_http_response{
    public function cookie(){}

    public function rawcookie(){}

    public function status(){}

    public function gzip(){}

    public function header(){}

    public function write(){}

    public function end(){}

    public function sendfile(){}

    public function __destruct(){}


}
/**
*@since 1.8.11
*/
class swoole\http\response{
    public function cookie(){}

    public function rawcookie(){}

    public function status(){}

    public function gzip(){}

    public function header(){}

    public function write(){}

    public function end(){}

    public function sendfile(){}

    public function __destruct(){}


}
/**
*@since 1.8.11
*/
class swoole_http_request{
    public function rawcontent(){}

    public function __destruct(){}


}
/**
*@since 1.8.11
*/
class swoole\http\request{
    public function rawcontent(){}

    public function __destruct(){}


}
/**
*@since 1.8.11
*/
class swoole_buffer{
    public function __construct(){}

    public function __destruct(){}

    public function __toString(){}

    public function substr(){}

    public function write(){}

    public function read(){}

    public function append(){}

    public function expand(){}

    public function clear(){}


}
/**
*@since 1.8.11
*/
class swoole\buffer{
    public function __construct(){}

    public function __destruct(){}

    public function __toString(){}

    public function substr(){}

    public function write(){}

    public function read(){}

    public function append(){}

    public function expand(){}

    public function clear(){}


}
/**
*@since 1.8.11
*/
class swoole_websocket_server extends swoole_http_server{
    /**
    * @param $event_name[required]
    * @param $callback[required]
    */
    public function on($event_name,$callback){}

    /**
    * @param $fd[required]
    * @param $data[required]
    * @param $opcode[optional]
    * @param $finish[optional]
    */
    public function push($fd,$data,$opcode=null,$finish=null){}

    /**
    * @param $fd[required]
    */
    public function exist($fd){}

    /**
    * @param $data[required]
    * @param $opcode[optional]
    * @param $finish[optional]
    * @param $mask[optional]
    */
    public static function pack($data,$opcode=null,$finish=null,$mask=null){}

    /**
    * @param $data[required]
    */
    public static function unpack($data){}

    public function start(){}

    /**
    * @param $host[required]
    * @param $port[required]
    * @param $mode[optional]
    * @param $sock_type[optional]
    */
    public function __construct($host,$port,$mode=null,$sock_type=null){}

    /**
    * @param $host[required]
    * @param $port[required]
    * @param $sock_type[required]
    */
    public function listen($host,$port,$sock_type){}

    /**
    * @param $host[required]
    * @param $port[required]
    * @param $sock_type[required]
    */
    public function addlistener($host,$port,$sock_type){}

    /**
    * @param $zset[required]
    */
    public function set($zset){}

    /**
    * @param $fd[required]
    * @param $send_data[required]
    * @param $from_id[optional]
    */
    public function send($fd,$send_data,$from_id=null){}

    /**
    * @param $ip[required]
    * @param $port[required]
    * @param $send_data[optional]
    */
    public function sendto($ip,$port,$send_data=null){}

    /**
    * @param $conn_fd[required]
    * @param $send_data[required]
    */
    public function sendwait($conn_fd,$send_data){}

    /**
    * @param $fd[required]
    * @param $is_protected[optional]
    */
    public function protect($fd,$is_protected=null){}

    /**
    * @param $conn_fd[required]
    * @param $filename[required]
    */
    public function sendfile($conn_fd,$filename){}

    /**
    * @param $fd[required]
    */
    public function close($fd){}

    /**
    * @param $fd[required]
    */
    public function confirm($fd){}

    /**
    * @param $fd[required]
    */
    public function pause($fd){}

    /**
    * @param $fd[required]
    */
    public function resume($fd){}

    /**
    * @param $data[required]
    * @param $worker_id[required]
    */
    public function task($data,$worker_id){}

    /**
    * @param $data[required]
    * @param $timeout[optional]
    * @param $worker_id[optional]
    */
    public function taskwait($data,$timeout=null,$worker_id=null){}

    /**
    * @param $tasks[required]
    * @param $timeout[optional]
    */
    public function taskWaitMulti($tasks,$timeout=null){}

    /**
    * @param $data[required]
    */
    public function finish($data){}

    public function reload(){}

    public function shutdown(){}

    public function stop(){}

    public function getLastError(){}

    /**
    * @param $from_id[required]
    */
    public function heartbeat($from_id){}

    /**
    * @param $fd[required]
    * @param $from_id[required]
    */
    public function connection_info($fd,$from_id){}

    /**
    * @param $start_fd[required]
    * @param $find_count[required]
    */
    public function connection_list($start_fd,$find_count){}

    /**
    * @param $fd[required]
    * @param $from_id[required]
    */
    public function getClientInfo($fd,$from_id){}

    /**
    * @param $start_fd[required]
    * @param $find_count[required]
    */
    public function getClientList($start_fd,$find_count){}

    /**
    * @param $ms[required]
    * @param $callback[required]
    * @param $param[optional]
    */
    public function after($ms,$callback,$param=null){}

    /**
    * @param $ms[required]
    * @param $callback[required]
    */
    public function tick($ms,$callback){}

    /**
    * @param $timer_id[required]
    */
    public function clearTimer($timer_id){}

    /**
    * @param $callback[required]
    */
    public function defer($callback){}

    public function sendMessage(){}

    public function addProcess(){}

    public function stats(){}

    /**
    * @param $fd[required]
    * @param $uid[required]
    */
    public function bind($fd,$uid){}


}
/**
*@since 1.8.11
*/
class swoole\websocket\server extends swoole_http_server{
    /**
    * @param $event_name[required]
    * @param $callback[required]
    */
    public function on($event_name,$callback){}

    /**
    * @param $fd[required]
    * @param $data[required]
    * @param $opcode[optional]
    * @param $finish[optional]
    */
    public function push($fd,$data,$opcode=null,$finish=null){}

    /**
    * @param $fd[required]
    */
    public function exist($fd){}

    /**
    * @param $data[required]
    * @param $opcode[optional]
    * @param $finish[optional]
    * @param $mask[optional]
    */
    public static function pack($data,$opcode=null,$finish=null,$mask=null){}

    /**
    * @param $data[required]
    */
    public static function unpack($data){}

    public function start(){}

    /**
    * @param $host[required]
    * @param $port[required]
    * @param $mode[optional]
    * @param $sock_type[optional]
    */
    public function __construct($host,$port,$mode=null,$sock_type=null){}

    /**
    * @param $host[required]
    * @param $port[required]
    * @param $sock_type[required]
    */
    public function listen($host,$port,$sock_type){}

    /**
    * @param $host[required]
    * @param $port[required]
    * @param $sock_type[required]
    */
    public function addlistener($host,$port,$sock_type){}

    /**
    * @param $zset[required]
    */
    public function set($zset){}

    /**
    * @param $fd[required]
    * @param $send_data[required]
    * @param $from_id[optional]
    */
    public function send($fd,$send_data,$from_id=null){}

    /**
    * @param $ip[required]
    * @param $port[required]
    * @param $send_data[optional]
    */
    public function sendto($ip,$port,$send_data=null){}

    /**
    * @param $conn_fd[required]
    * @param $send_data[required]
    */
    public function sendwait($conn_fd,$send_data){}

    /**
    * @param $fd[required]
    * @param $is_protected[optional]
    */
    public function protect($fd,$is_protected=null){}

    /**
    * @param $conn_fd[required]
    * @param $filename[required]
    */
    public function sendfile($conn_fd,$filename){}

    /**
    * @param $fd[required]
    */
    public function close($fd){}

    /**
    * @param $fd[required]
    */
    public function confirm($fd){}

    /**
    * @param $fd[required]
    */
    public function pause($fd){}

    /**
    * @param $fd[required]
    */
    public function resume($fd){}

    /**
    * @param $data[required]
    * @param $worker_id[required]
    */
    public function task($data,$worker_id){}

    /**
    * @param $data[required]
    * @param $timeout[optional]
    * @param $worker_id[optional]
    */
    public function taskwait($data,$timeout=null,$worker_id=null){}

    /**
    * @param $tasks[required]
    * @param $timeout[optional]
    */
    public function taskWaitMulti($tasks,$timeout=null){}

    /**
    * @param $data[required]
    */
    public function finish($data){}

    public function reload(){}

    public function shutdown(){}

    public function stop(){}

    public function getLastError(){}

    /**
    * @param $from_id[required]
    */
    public function heartbeat($from_id){}

    /**
    * @param $fd[required]
    * @param $from_id[required]
    */
    public function connection_info($fd,$from_id){}

    /**
    * @param $start_fd[required]
    * @param $find_count[required]
    */
    public function connection_list($start_fd,$find_count){}

    /**
    * @param $fd[required]
    * @param $from_id[required]
    */
    public function getClientInfo($fd,$from_id){}

    /**
    * @param $start_fd[required]
    * @param $find_count[required]
    */
    public function getClientList($start_fd,$find_count){}

    /**
    * @param $ms[required]
    * @param $callback[required]
    * @param $param[optional]
    */
    public function after($ms,$callback,$param=null){}

    /**
    * @param $ms[required]
    * @param $callback[required]
    */
    public function tick($ms,$callback){}

    /**
    * @param $timer_id[required]
    */
    public function clearTimer($timer_id){}

    /**
    * @param $callback[required]
    */
    public function defer($callback){}

    public function sendMessage(){}

    public function addProcess(){}

    public function stats(){}

    /**
    * @param $fd[required]
    * @param $uid[required]
    */
    public function bind($fd,$uid){}


}
/**
*@since 1.8.11
*/
class swoole_websocket_frame{

}
/**
*@since 1.8.11
*/
class swoole\websocket\frame{

}
/**
*@since 1.8.11
*/
class swoole_mysql{
    public function __construct(){}

    public function __destruct(){}

    public function connect(){}

    public function query(){}

    public function close(){}

    public function on(){}


}
/**
*@since 1.8.11
*/
class swoole\mysql{
    public function __construct(){}

    public function __destruct(){}

    public function connect(){}

    public function query(){}

    public function close(){}

    public function on(){}


}
/**
*@since 1.8.11
*/
class swoole_mysql_exception extends Exception{
    final private function __clone(){}

    /**
    * @param $message[optional]
    * @param $code[optional]
    * @param $previous[optional]
    */
    public function __construct($message=null,$code=null,$previous=null){}

    public function __wakeup(){}

    final public function getMessage(){}

    final public function getCode(){}

    final public function getFile(){}

    final public function getLine(){}

    final public function getTrace(){}

    final public function getPrevious(){}

    final public function getTraceAsString(){}

    public function __toString(){}


}
/**
*@since 1.8.11
*/
class swoole\mysql\exception extends Exception{
    final private function __clone(){}

    /**
    * @param $message[optional]
    * @param $code[optional]
    * @param $previous[optional]
    */
    public function __construct($message=null,$code=null,$previous=null){}

    public function __wakeup(){}

    final public function getMessage(){}

    final public function getCode(){}

    final public function getFile(){}

    final public function getLine(){}

    final public function getTrace(){}

    final public function getPrevious(){}

    final public function getTraceAsString(){}

    public function __toString(){}


}
/**
*@since 1.8.11
*/
class swoole_module{
    /**
    * @param $func[required]
    * @param $params[required]
    */
    public function __call($func,$params){}


}
/**
*@since 1.8.11
*/
class swoole\module{
    /**
    * @param $func[required]
    * @param $params[required]
    */
    public function __call($func,$params){}


}

define('RUNKIT_IMPORT_FUNCTIONS',1);
define('RUNKIT_IMPORT_CLASS_METHODS',2);
define('RUNKIT_IMPORT_CLASS_CONSTS',4);
define('RUNKIT_IMPORT_CLASS_PROPS',8);
define('RUNKIT_IMPORT_CLASS_STATIC_PROPS',16);
define('RUNKIT_IMPORT_CLASSES',30);
define('RUNKIT_IMPORT_OVERRIDE',32);
define('RUNKIT_VERSION','1.0.4');
define('RUNKIT_ACC_RETURN_REFERENCE',67108864);
define('RUNKIT_ACC_PUBLIC',256);
define('RUNKIT_ACC_PROTECTED',512);
define('RUNKIT_ACC_PRIVATE',1024);
define('RUNKIT_ACC_STATIC',1);
define('RUNKIT_OVERRIDE_OBJECTS',32768);
define('RUNKIT_FEATURE_MANIPULATION','1');
define('RUNKIT_FEATURE_SUPERGLOBALS','1');
function runkit_zval_inspect(){}

function runkit_object_id(){}

function runkit_return_value_used(){}

function runkit_superglobals(){}

function runkit_function_add(){}

function runkit_function_remove(){}

function runkit_function_rename(){}

function runkit_function_redefine(){}

function runkit_function_copy(){}

function runkit_method_add(){}

function runkit_method_redefine(){}

function runkit_method_remove(){}

function runkit_method_rename(){}

function runkit_method_copy(){}

function runkit_constant_redefine(){}

function runkit_constant_remove(){}

function runkit_constant_add(){}


/**
*@since 2.2.0
*/
class Memcached{
    /**
    * @param $persistent_id[optional]
    * @param $callback[optional]
    */
    public function __construct($persistent_id=null,$callback=null){}

    public function getResultCode(){}

    public function getResultMessage(){}

    /**
    * @param $key[required]
    * @param $cache_cb[optional]
    * @param $cas_token[optional]
    * @param $udf_flags[optional]
    */
    public function get($key,$cache_cb=null,$cas_token=null,$udf_flags=null){}

    /**
    * @param $server_key[required]
    * @param $key[required]
    * @param $cache_cb[optional]
    * @param $cas_token[optional]
    * @param $udf_flags[optional]
    */
    public function getByKey($server_key,$key,$cache_cb=null,$cas_token=null,$udf_flags=null){}

    /**
    * @param $keys[required]
    * @param $cas_tokens[optional]
    * @param $flags[optional]
    * @param $udf_flags[optional]
    */
    public function getMulti($keys,$cas_tokens=null,$flags=null,$udf_flags=null){}

    /**
    * @param $server_key[required]
    * @param $keys[required]
    * @param $cas_tokens[optional]
    * @param $flags[optional]
    * @param $udf_flags[optional]
    */
    public function getMultiByKey($server_key,$keys,$cas_tokens=null,$flags=null,$udf_flags=null){}

    /**
    * @param $keys[required]
    * @param $with_cas[optional]
    * @param $value_cb[optional]
    */
    public function getDelayed($keys,$with_cas=null,$value_cb=null){}

    /**
    * @param $server_key[required]
    * @param $keys[required]
    * @param $with_cas[optional]
    * @param $value_cb[optional]
    */
    public function getDelayedByKey($server_key,$keys,$with_cas=null,$value_cb=null){}

    public function fetch(){}

    public function fetchAll(){}

    /**
    * @param $key[required]
    * @param $value[required]
    * @param $expiration[optional]
    * @param $udf_flags[optional]
    */
    public function set($key,$value,$expiration=null,$udf_flags=null){}

    /**
    * @param $server_key[required]
    * @param $key[required]
    * @param $value[required]
    * @param $expiration[optional]
    * @param $udf_flags[optional]
    */
    public function setByKey($server_key,$key,$value,$expiration=null,$udf_flags=null){}

    /**
    * @param $key[required]
    * @param $expiration[required]
    */
    public function touch($key,$expiration){}

    /**
    * @param $server_key[required]
    * @param $key[required]
    * @param $expiration[required]
    */
    public function touchByKey($server_key,$key,$expiration){}

    /**
    * @param $items[required]
    * @param $expiration[optional]
    * @param $udf_flags[optional]
    */
    public function setMulti($items,$expiration=null,$udf_flags=null){}

    /**
    * @param $server_key[required]
    * @param $items[required]
    * @param $expiration[optional]
    * @param $udf_flags[optional]
    */
    public function setMultiByKey($server_key,$items,$expiration=null,$udf_flags=null){}

    /**
    * @param $cas_token[required]
    * @param $key[required]
    * @param $value[required]
    * @param $expiration[optional]
    * @param $udf_flags[optional]
    */
    public function cas($cas_token,$key,$value,$expiration=null,$udf_flags=null){}

    /**
    * @param $cas_token[required]
    * @param $server_key[required]
    * @param $key[required]
    * @param $value[required]
    * @param $expiration[optional]
    * @param $udf_flags[optional]
    */
    public function casByKey($cas_token,$server_key,$key,$value,$expiration=null,$udf_flags=null){}

    /**
    * @param $key[required]
    * @param $value[required]
    * @param $expiration[optional]
    * @param $udf_flags[optional]
    */
    public function add($key,$value,$expiration=null,$udf_flags=null){}

    /**
    * @param $server_key[required]
    * @param $key[required]
    * @param $value[required]
    * @param $expiration[optional]
    * @param $udf_flags[optional]
    */
    public function addByKey($server_key,$key,$value,$expiration=null,$udf_flags=null){}

    /**
    * @param $key[required]
    * @param $value[required]
    * @param $expiration[optional]
    */
    public function append($key,$value,$expiration=null){}

    /**
    * @param $server_key[required]
    * @param $key[required]
    * @param $value[required]
    * @param $expiration[optional]
    */
    public function appendByKey($server_key,$key,$value,$expiration=null){}

    /**
    * @param $key[required]
    * @param $value[required]
    * @param $expiration[optional]
    */
    public function prepend($key,$value,$expiration=null){}

    /**
    * @param $server_key[required]
    * @param $key[required]
    * @param $value[required]
    * @param $expiration[optional]
    */
    public function prependByKey($server_key,$key,$value,$expiration=null){}

    /**
    * @param $key[required]
    * @param $value[required]
    * @param $expiration[optional]
    * @param $udf_flags[optional]
    */
    public function replace($key,$value,$expiration=null,$udf_flags=null){}

    /**
    * @param $server_key[required]
    * @param $key[required]
    * @param $value[required]
    * @param $expiration[optional]
    * @param $udf_flags[optional]
    */
    public function replaceByKey($server_key,$key,$value,$expiration=null,$udf_flags=null){}

    /**
    * @param $key[required]
    * @param $time[optional]
    */
    public function delete($key,$time=null){}

    /**
    * @param $keys[required]
    * @param $time[optional]
    */
    public function deleteMulti($keys,$time=null){}

    /**
    * @param $server_key[required]
    * @param $key[required]
    * @param $time[optional]
    */
    public function deleteByKey($server_key,$key,$time=null){}

    /**
    * @param $server_key[required]
    * @param $keys[required]
    * @param $time[optional]
    */
    public function deleteMultiByKey($server_key,$keys,$time=null){}

    /**
    * @param $key[required]
    * @param $offset[optional]
    * @param $initial_value[optional]
    * @param $expiry[optional]
    */
    public function increment($key,$offset=null,$initial_value=null,$expiry=null){}

    /**
    * @param $key[required]
    * @param $offset[optional]
    * @param $initial_value[optional]
    * @param $expiry[optional]
    */
    public function decrement($key,$offset=null,$initial_value=null,$expiry=null){}

    /**
    * @param $server_key[required]
    * @param $key[required]
    * @param $offset[optional]
    * @param $initial_value[optional]
    * @param $expiry[optional]
    */
    public function incrementByKey($server_key,$key,$offset=null,$initial_value=null,$expiry=null){}

    /**
    * @param $server_key[required]
    * @param $key[required]
    * @param $offset[optional]
    * @param $initial_value[optional]
    * @param $expiry[optional]
    */
    public function decrementByKey($server_key,$key,$offset=null,$initial_value=null,$expiry=null){}

    /**
    * @param $host[required]
    * @param $port[required]
    * @param $weight[optional]
    */
    public function addServer($host,$port,$weight=null){}

    /**
    * @param $servers[required]
    */
    public function addServers($servers){}

    public function getServerList(){}

    /**
    * @param $server_key[required]
    */
    public function getServerByKey($server_key){}

    public function resetServerList(){}

    public function quit(){}

    public function flushBuffers(){}

    public function getLastErrorMessage(){}

    public function getLastErrorCode(){}

    public function getLastErrorErrno(){}

    public function getLastDisconnectedServer(){}

    public function getStats(){}

    public function getVersion(){}

    public function getAllKeys(){}

    /**
    * @param $delay[optional]
    */
    public function flush($delay=null){}

    /**
    * @param $option[required]
    */
    public function getOption($option){}

    /**
    * @param $option[required]
    * @param $value[required]
    */
    public function setOption($option,$value){}

    /**
    * @param $options[required]
    */
    public function setOptions($options){}

    /**
    * @param $host_map[required]
    * @param $forward_map[required]
    * @param $replicas[required]
    */
    public function setBucket($host_map,$forward_map,$replicas){}

    /**
    * @param $username[required]
    * @param $password[required]
    */
    public function setSaslAuthData($username,$password){}

    public function isPersistent(){}

    public function isPristine(){}


}
/**
*@since 2.2.0
*/
class MemcachedException extends RuntimeException{
    final private function __clone(){}

    /**
    * @param $message[optional]
    * @param $code[optional]
    * @param $previous[optional]
    */
    public function __construct($message=null,$code=null,$previous=null){}

    public function __wakeup(){}

    final public function getMessage(){}

    final public function getCode(){}

    final public function getFile(){}

    final public function getLine(){}

    final public function getTrace(){}

    final public function getPrevious(){}

    final public function getTraceAsString(){}

    public function __toString(){}


}

/**
* @param $val[required]
* @param $simple[optional]
*/
function hprose_serialize($val,$simple=null){}

/**
* @param $data[required]
* @param $simple[optional]
*/
function hprose_unserialize($data,$simple=null){}

function hprose_info(){}

/**
*@since 1.6.5
*/
class Hprose\Tags{

}
/**
*@since 1.6.5
*/
class hprosetags{

}
/**
*@since 1.6.5
*/
class Hprose\BytesIO{
    /**
    * @param $str[optional]
    */
    public function __construct($str=null){}

    public function close(){}

    public function length(){}

    public function getc(){}

    /**
    * @param $n[required]
    */
    public function read($n){}

    public function readfull(){}

    /**
    * @param $tag[required]
    */
    public function readuntil($tag){}

    /**
    * @param $n[required]
    */
    public function readString($n){}

    public function mark(){}

    public function unmark(){}

    public function reset(){}

    /**
    * @param $n[required]
    */
    public function skip($n){}

    public function eof(){}

    /**
    * @param $str[required]
    * @param $n[optional]
    */
    public function write($str,$n=null){}

    /**
    * @param $filename[required]
    */
    public function load($filename){}

    /**
    * @param $filename[required]
    */
    public function save($filename){}

    public function toString(){}

    public function __toString(){}


}
/**
*@since 1.6.5
*/
class hprosebytesio{
    /**
    * @param $str[optional]
    */
    public function __construct($str=null){}

    public function close(){}

    public function length(){}

    public function getc(){}

    /**
    * @param $n[required]
    */
    public function read($n){}

    public function readfull(){}

    /**
    * @param $tag[required]
    */
    public function readuntil($tag){}

    /**
    * @param $n[required]
    */
    public function readString($n){}

    public function mark(){}

    public function unmark(){}

    public function reset(){}

    /**
    * @param $n[required]
    */
    public function skip($n){}

    public function eof(){}

    /**
    * @param $str[required]
    * @param $n[optional]
    */
    public function write($str,$n=null){}

    /**
    * @param $filename[required]
    */
    public function load($filename){}

    /**
    * @param $filename[required]
    */
    public function save($filename){}

    public function toString(){}

    public function __toString(){}


}
/**
*@since 1.6.5
*/
class Hprose\ClassManager{
    /**
    * @param $name[required]
    * @param $alias[required]
    */
    public static function register($name,$alias){}

    /**
    * @param $name[required]
    */
    public static function getAlias($name){}

    /**
    * @param $alias[required]
    */
    public static function getClass($alias){}


}
/**
*@since 1.6.5
*/
class hproseclassmanager{
    /**
    * @param $name[required]
    * @param $alias[required]
    */
    public static function register($name,$alias){}

    /**
    * @param $name[required]
    */
    public static function getAlias($name){}

    /**
    * @param $alias[required]
    */
    public static function getClass($alias){}


}
/**
*@since 1.6.5
*/
class Hprose\Writer{
    /**
    * @param $stream[required]
    * @param $simple[optional]
    */
    public function __construct($stream,$simple=null){}

    /**
    * @param $data[required]
    */
    public function serialize($data){}

    /**
    * @param $i[required]
    */
    public function writeInteger($i){}

    /**
    * @param $i[required]
    */
    public function writeLong($i){}

    /**
    * @param $d[required]
    */
    public function writeDouble($d){}

    public function writeNaN(){}

    /**
    * @param $positive[optional]
    */
    public function writeInfinity($positive=null){}

    public function writeNull(){}

    public function writeEmpty(){}

    /**
    * @param $b[required]
    */
    public function writeBoolean($b){}

    /**
    * @param $ch[required]
    */
    public function writeUTF8Char($ch){}

    /**
    * @param $str[required]
    */
    public function writeString($str){}

    /**
    * @param $str[required]
    */
    public function writeStringWithRef($str){}

    /**
    * @param $bytes[required]
    */
    public function writeBytes($bytes){}

    /**
    * @param $bytes[required]
    */
    public function writeBytesWithRef($bytes){}

    /**
    * @param $dt[required]
    */
    public function writeBytesIO($dt){}

    /**
    * @param $dt[required]
    */
    public function writeBytesIOWithRef($dt){}

    /**
    * @param $dt[required]
    */
    public function writeDateTime($dt){}

    /**
    * @param $dt[required]
    */
    public function writeDateTimeWithRef($dt){}

    /**
    * @param $arr[required]
    */
    public function writeArray($arr){}

    /**
    * @param $arr[required]
    */
    public function writeAssocArray($arr){}

    /**
    * @param $list[required]
    */
    public function writeList($list){}

    /**
    * @param $list[required]
    */
    public function writeListWithRef($list){}

    /**
    * @param $map[required]
    */
    public function writeMap($map){}

    /**
    * @param $map[required]
    */
    public function writeMapWithRef($map){}

    /**
    * @param $obj[required]
    */
    public function writeStdClass($obj){}

    /**
    * @param $obj[required]
    */
    public function writeStdClassWithRef($obj){}

    /**
    * @param $obj[required]
    */
    public function writeObject($obj){}

    /**
    * @param $obj[required]
    */
    public function writeObjectWithRef($obj){}

    public function reset(){}


}
/**
*@since 1.6.5
*/
class hprosewriter{
    /**
    * @param $stream[required]
    * @param $simple[optional]
    */
    public function __construct($stream,$simple=null){}

    /**
    * @param $data[required]
    */
    public function serialize($data){}

    /**
    * @param $i[required]
    */
    public function writeInteger($i){}

    /**
    * @param $i[required]
    */
    public function writeLong($i){}

    /**
    * @param $d[required]
    */
    public function writeDouble($d){}

    public function writeNaN(){}

    /**
    * @param $positive[optional]
    */
    public function writeInfinity($positive=null){}

    public function writeNull(){}

    public function writeEmpty(){}

    /**
    * @param $b[required]
    */
    public function writeBoolean($b){}

    /**
    * @param $ch[required]
    */
    public function writeUTF8Char($ch){}

    /**
    * @param $str[required]
    */
    public function writeString($str){}

    /**
    * @param $str[required]
    */
    public function writeStringWithRef($str){}

    /**
    * @param $bytes[required]
    */
    public function writeBytes($bytes){}

    /**
    * @param $bytes[required]
    */
    public function writeBytesWithRef($bytes){}

    /**
    * @param $dt[required]
    */
    public function writeBytesIO($dt){}

    /**
    * @param $dt[required]
    */
    public function writeBytesIOWithRef($dt){}

    /**
    * @param $dt[required]
    */
    public function writeDateTime($dt){}

    /**
    * @param $dt[required]
    */
    public function writeDateTimeWithRef($dt){}

    /**
    * @param $arr[required]
    */
    public function writeArray($arr){}

    /**
    * @param $arr[required]
    */
    public function writeAssocArray($arr){}

    /**
    * @param $list[required]
    */
    public function writeList($list){}

    /**
    * @param $list[required]
    */
    public function writeListWithRef($list){}

    /**
    * @param $map[required]
    */
    public function writeMap($map){}

    /**
    * @param $map[required]
    */
    public function writeMapWithRef($map){}

    /**
    * @param $obj[required]
    */
    public function writeStdClass($obj){}

    /**
    * @param $obj[required]
    */
    public function writeStdClassWithRef($obj){}

    /**
    * @param $obj[required]
    */
    public function writeObject($obj){}

    /**
    * @param $obj[required]
    */
    public function writeObjectWithRef($obj){}

    public function reset(){}


}
/**
*@since 1.6.5
*/
class Hprose\RawReader{
    public function __construct(){}

    public function readRaw(){}


}
/**
*@since 1.6.5
*/
class hproserawreader{
    public function __construct(){}

    public function readRaw(){}


}
/**
*@since 1.6.5
*/
class Hprose\Reader extends Hprose\RawReader{
    /**
    * @param $stream[required]
    * @param $simple[optional]
    */
    public function __construct($stream,$simple=null){}

    public function unserialize(){}

    /**
    * @param $expectTag[required]
    * @param $tag[optional]
    */
    public function checkTag($expectTag,$tag=null){}

    /**
    * @param $expectTags[required]
    * @param $tag[optional]
    */
    public function checkTags($expectTags,$tag=null){}

    public function readIntegerWithoutTag(){}

    public function readInteger(){}

    public function readLongWithoutTag(){}

    public function readLong(){}

    public function readDoubleWithoutTag(){}

    public function readDouble(){}

    public function readNaN(){}

    public function readInfinityWithoutTag(){}

    public function readInfinity(){}

    public function readNull(){}

    public function readEmpty(){}

    public function readBoolean(){}

    public function readDateWithoutTag(){}

    public function readDate(){}

    public function readTimeWithoutTag(){}

    public function readTime(){}

    public function readBytesWithoutTag(){}

    public function readBytes(){}

    public function readUTF8CharWithoutTag(){}

    public function readUTF8Char(){}

    public function readStringWithoutTag(){}

    public function readString(){}

    public function readGuidWithoutTag(){}

    public function readGuid(){}

    public function readListWithoutTag(){}

    public function readList(){}

    public function readMapWithoutTag(){}

    public function readMap(){}

    public function readObjectWithoutTag(){}

    public function readObject(){}

    public function reset(){}

    public function readRaw(){}


}
/**
*@since 1.6.5
*/
class hprosereader extends Hprose\RawReader{
    /**
    * @param $stream[required]
    * @param $simple[optional]
    */
    public function __construct($stream,$simple=null){}

    public function unserialize(){}

    /**
    * @param $expectTag[required]
    * @param $tag[optional]
    */
    public function checkTag($expectTag,$tag=null){}

    /**
    * @param $expectTags[required]
    * @param $tag[optional]
    */
    public function checkTags($expectTags,$tag=null){}

    public function readIntegerWithoutTag(){}

    public function readInteger(){}

    public function readLongWithoutTag(){}

    public function readLong(){}

    public function readDoubleWithoutTag(){}

    public function readDouble(){}

    public function readNaN(){}

    public function readInfinityWithoutTag(){}

    public function readInfinity(){}

    public function readNull(){}

    public function readEmpty(){}

    public function readBoolean(){}

    public function readDateWithoutTag(){}

    public function readDate(){}

    public function readTimeWithoutTag(){}

    public function readTime(){}

    public function readBytesWithoutTag(){}

    public function readBytes(){}

    public function readUTF8CharWithoutTag(){}

    public function readUTF8Char(){}

    public function readStringWithoutTag(){}

    public function readString(){}

    public function readGuidWithoutTag(){}

    public function readGuid(){}

    public function readListWithoutTag(){}

    public function readList(){}

    public function readMapWithoutTag(){}

    public function readMap(){}

    public function readObjectWithoutTag(){}

    public function readObject(){}

    public function reset(){}

    public function readRaw(){}


}
/**
*@since 1.6.5
*/
class Hprose\Formatter{
    /**
    * @param $val[required]
    * @param $simple[optional]
    */
    public static function serialize($val,$simple=null){}

    /**
    * @param $data[required]
    * @param $simple[optional]
    */
    public static function unserialize($data,$simple=null){}


}
/**
*@since 1.6.5
*/
class hproseformatter{
    /**
    * @param $val[required]
    * @param $simple[optional]
    */
    public static function serialize($val,$simple=null){}

    /**
    * @param $data[required]
    * @param $simple[optional]
    */
    public static function unserialize($data,$simple=null){}


}

