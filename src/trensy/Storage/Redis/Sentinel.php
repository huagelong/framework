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
namespace Trensy\Storage\Redis;

class Sentinel
{
    /**
     * Contains a client that connects to a Sentinel node.
     * Sentinel uses the same protocol as Redis which makes using Client convenient.
     * @var Client
     */
    protected $_client;

    /**
     * Contains an active instance of Cluster per master pool
     * @var array
     */
    protected $_cluster = array();

    /**
     * Contains an active instance of Client representing a master
     * @var array
     */
    protected $_master = array();

    /**
     * Contains an array Client objects representing all slaves per master pool
     * @var array
     */
    protected $_slaves = array();

    /**
     * Use the phpredis extension or the standalone implementation
     * @var bool
     * @deprecated
     */
    protected $_standAlone = false;

    /**
     * Store the AUTH password used by Client instances
     * @var string
     */
    protected $_password = '';

    /**
     * Connect with a Sentinel node. Sentinel will do the master and slave discovery
     *
     * @param Client $client
     * @param string $password (deprecated - use setClientPassword)
     * @throws Exception
     */
    public function __construct(Client $client, $password = NULL)
    {
        if(!$client instanceof Client){
            throw new Exception('Sentinel client should be an instance of Client');
        }
        $client->forceStandalone(); // SENTINEL command not currently supported by phpredis
        $this->_client     = $client;
        $this->_password   = $password;
        $this->_timeout    = NULL;
        $this->_persistent = '';
        $this->_db         = 0;
    }

    /**
     * @param float $timeout
     * @return $this
     */
    public function setClientTimeout($timeout)
    {
        $this->_timeout = $timeout;
        return $this;
    }

    /**
     * @param string $persistent
     * @return $this
     */
    public function setClientPersistent($persistent)
    {
        $this->_persistent = $persistent;
        return $this;
    }

    /**
     * @param int $db
     * @return $this
     */
    public function setClientDatabase($db)
    {
        $this->_db = $db;
        return $this;
    }

    /**
     * @param null|string $password
     * @return $this
     */
    public function setClientPassword($password)
    {
        $this->_password = $password;
        return $this;
    }

    /**
     * @return Sentinel
     * @deprecated
     */
    public function forceStandalone()
    {
        $this->_standAlone = true;
        return $this;
    }

    /**
     * Discover the master node automatically and return an instance of Client that connects to the master
     *
     * @param string $name
     * @return Client
     * @throws Exception
     */
    public function createMasterClient($name)
    {
        $master = $this->getMasterAddressByName($name);
        if(!isset($master[0]) || !isset($master[1])){
            throw new Exception('Master not found');
        }
        return new Client($master[0], $master[1], $this->_timeout, $this->_persistent, $this->_db, $this->_password);
    }

    /**
     * If a Client object exists for a master, return it. Otherwise create one and return it
     * @param string $name
     * @return Client
     */
    public function getMasterClient($name)
    {
        if(!isset($this->_master[$name])){
            $this->_master[$name] = $this->createMasterClient($name);
        }
        return $this->_master[$name];
    }

    /**
     * Discover the slave nodes automatically and return an array of Client objects
     *
     * @param string $name
     * @return Client[]
     * @throws Exception
     */
    public function createSlaveClients($name)
    {
        $slaves = $this->slaves($name);
        $workingSlaves = array();
        foreach($slaves as $slave) {
            if(!isset($slave[9])){
                throw new Exception('Can\' retrieve slave status');
            }
            if(!strstr($slave[9],'s_down') && !strstr($slave[9],'disconnected')) {
                $workingSlaves[] = new Client($slave[3], $slave[5], $this->_timeout, $this->_persistent, $this->_db, $this->_password);
            }
        }
        return $workingSlaves;
    }

    /**
     * If an array of Client objects exist for a set of slaves, return them. Otherwise create and return them
     * @param string $name
     * @return Client[]
     */
    public function getSlaveClients($name)
    {
        if(!isset($this->_slaves[$name])){
            $this->_slaves[$name] = $this->createSlaveClients($name);
        }
        return $this->_slaves[$name];
    }

    /**
     * Returns a Redis cluster object containing a random slave and the master
     * When $selectRandomSlave is true, only one random slave is passed.
     * When $selectRandomSlave is false, all clients are passed and hashing is applied in Cluster
     * When $writeOnly is false, the master server will also be used for read commands.
     *
     * @param string $name
     * @param int $db
     * @param int $replicas
     * @param bool $selectRandomSlave
     * @param bool $writeOnly
     * @return Cluster
     * @throws Exception
     * @deprecated
     */
    public function createCluster($name, $db=0, $replicas=128, $selectRandomSlave=true, $writeOnly=false)
    {
        $clients = array();
        $workingClients = array();
        $master = $this->master($name);
        if(strstr($master[9],'s_down') || strstr($master[9],'disconnected')) {
            throw new Exception('The master is down');
        }
        $slaves = $this->slaves($name);
        foreach($slaves as $slave){
            if(!strstr($slave[9],'s_down') && !strstr($slave[9],'disconnected')) {
                $workingClients[] =  array('host'=>$slave[3],'port'=>$slave[5],'master'=>false,'db'=>$db,'password'=>$this->_password);
            }
        }
        if(count($workingClients)>0){
            if($selectRandomSlave){
                if(!$writeOnly){
                    $workingClients[] = array('host'=>$master[3],'port'=>$master[5],'master'=>false,'db'=>$db,'password'=>$this->_password);
                }
                $clients[] = $workingClients[rand(0,count($workingClients)-1)];
            } else {
                $clients = $workingClients;
            }
        }
        $clients[] = array('host'=>$master[3],'port'=>$master[5], 'db'=>$db ,'master'=>true,'write_only'=>$writeOnly,'password'=>$this->_password);
        return new Cluster($clients,$replicas,$this->_standAlone);
    }

    /**
     * If a Cluster object exists, return it. Otherwise create one and return it.
     * @param string $name
     * @param int $db
     * @param int $replicas
     * @param bool $selectRandomSlave
     * @param bool $writeOnly
     * @return Cluster
     * @deprecated
     */
    public function getCluster($name, $db=0, $replicas=128, $selectRandomSlave=true, $writeOnly=false)
    {
        if(!isset($this->_cluster[$name])){
            $this->_cluster[$name] = $this->createCluster($name, $db, $replicas, $selectRandomSlave, $writeOnly);
        }
        return $this->_cluster[$name];
    }

    /**
     * Catch-all method
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        array_unshift($args,$name);
        return call_user_func(array($this->_client,'sentinel'),$args);
    }

    /**
     * Return information about all registered master servers
     * @return mixed
     */
    public function masters()
    {
        return $this->_client->sentinel('masters');
    }

    /**
     * Return all information for slaves that are associated with a single master
     * @param string $name
     * @return mixed
     */
    public function slaves($name)
    {
        return $this->_client->sentinel('slaves',$name);
    }

    /**
     * Get the information for a specific master
     * @param string $name
     * @return mixed
     */
    public function master($name)
    {
        return $this->_client->sentinel('master',$name);
    }

    /**
     * Get the hostname and port for a specific master
     * @param string $name
     * @return mixed
     */
    public function getMasterAddressByName($name)
    {
        return $this->_client->sentinel('get-master-addr-by-name',$name);
    }

    /**
     * Check if the Sentinel is still responding
     * @param string $name
     * @return mixed
     */
    public function ping()
    {
        return $this->_client->ping();
    }

    /**
     * Perform an auto-failover which will re-elect another master and make the current master a slave
     * @param string $name
     * @return mixed
     */
    public function failover($name)
    {
        return $this->_client->sentinel('failover',$name);
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->_client->getHost();
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->_client->getPort();
    }
}
