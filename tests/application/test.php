<?php
define("ROOT_PATH", __DIR__);
require_once __DIR__ . "/../../vendor/autoload.php";

class test{


    function hello($a,$b)
    {
        yield $a+$b;
    }

    function get($a,$b){
        $rs = yield $this->hello($a, $b);
        var_dump($rs);
        return $rs;
    }

}

$obj= new test();
$result = $obj->get(2,3);
print_r($result);





