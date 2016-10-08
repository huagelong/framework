<?php

/**
 * User: Peter Wang
 * Date: 16/9/13
 * Time: 下午3:18
 */
namespace Trendi\Test\Rpc;

use Trendi\Rpc\Controller;

class Index extends Controller
{

    public function index($say, $test)
    {
       return $this->response($say.serialize($test));
    }
}