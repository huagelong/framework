<?php
define("ROOT_PATH", __DIR__);
require_once __DIR__ . "/../../vendor/autoload.php";

use Trendi\Config\Config;
use Predis\Client;
use Trendi\Coroutine\SystemCall;
use Trendi\Coroutine\Scheduler;
use Trendi\Coroutine\Task;

class test{


    function hello($a,$b)
    {
        yield;
        return $a+$b;
    }

    function get($c, $a,$b){
        $rs = yield $this->hello($a, $b);
        return $rs+$c;
    }

}

$obj= new test();

$data = SystemCall::newTask($obj->get(1,2,3));
dump($data->getTaskId());




