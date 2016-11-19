<?php
/**
 * User: Peter Wang
 * Date: 16/11/14
 * Time: 上午10:53
 */

namespace Trensy\Coroutine\Db;


use Trensy\Coroutine\Base\CoroutineBase;
use Trensy\Coroutine\Base\CoroutineResult;

class DbCoroutine implements CoroutineBase
{
    /**
     * @var $pdoPool
     */
    public $pdoPool;
    /**
     * @var data => ['sql'=>'','connType'=>'','func'=>''];
     */
    public $data;
    public $result;

    public function __construct($pdoPool)
    {
        $this->result = CoroutineResult::getInstance();
        $this->pdoPool = $pdoPool;
    }


    public function set($sql,$connType,$func){
        $this->data['sql'] = $sql;
        $this->data['connType'] = $connType;
        $this->data['func'] = $func;
        yield $this;
    }


    public function send(callable $callback)
    {
        $this->pdoPool->perform($callback, $this->data);
    }

    public function getResult()
    {
        return $this->result;
    }
}