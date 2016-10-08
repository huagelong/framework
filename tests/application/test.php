<?php
define("ROOT_PATH", __DIR__);
require_once __DIR__ . "/../../vendor/autoload.php";

class test{


    function hello($a,$b)
    {
        yield $a+$b;
    }

    function get($c, $a,$b){
        $rs = yield $this->hello($a, $b);
        return $rs+$c;
    }

}

$obj= new test();
$result = $obj->hello(2,3);
print_r($result);





