<?php
define("ROOT_PATH", __DIR__);
require_once __DIR__ . "/../../vendor/autoload.php";

use Trendi\Config\Config;
use Predis\Client;
use Trendi\Coroutine\SystemCall;
use Trendi\Coroutine\Scheduler;
use Trendi\Coroutine\Task;


class redistest{
    private $client = null;

    function __construct()
    {
        Config::setConfigPath(__DIR__."/config");
        $config = Config::get("redis");
        $servers = $config['servers'];
        $options = $config['options'];
        $this->client = new Client($servers, $options);
    }

    function w()
    {
        yield SystemCall::retval($this->client->set("wtest", "test"));
    }

    function r()
    {
        yield SystemCall::retval($this->client->get("wtest"));
    }
}

function useClass(){
    $obj = new redistest();
    yield $obj->w();
    $data = (yield $obj->r());
    dump($data);
}

$scheduler = new Scheduler;
$scheduler->newTask(useClass());
$scheduler->withIoPoll()->run();



