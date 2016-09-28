<?php
/**
 * User: Peter Wang
 * Date: 16/9/28
 * Time: 下午7:17
 */

namespace Trendi\Test\Lib\Middleware;

use Trendi\Http\Request;
use Trendi\Http\Response;

class Author
{

    public function perform(Request $request, Response $response)
    {
        $response->redirect("/index/test");
        return false;
    }

}