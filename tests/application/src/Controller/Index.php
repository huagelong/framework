<?php

/**
 * User: Peter Wang
 * Date: 16/9/13
 * Time: 下午3:18
 */
namespace Trendi\Test\Controller;

use Trendi\Foundation\Controller;
use Trendi\Http\Request;
use Trendi\Http\Response;

class Index
{

    public function index(Request $request, Response $response)
    {
        $say = $request->query->get("say");
        
        $response->view->say = $say;
        
        return $response->redirect("/index/test");

        return $response->render("index/index");
    }

    public function test(Request $request, Response $response)
    {
//        $this->test2();
//        dump(\Context::response());
//        dump(\Context::getFacadeApplication());

//        \Task::email("wangkaihui@putao.com",array('site-monitor@putao.com' => 'Site Monitor'),"test","hello world");

        $str = "
            asdasdaasd34gerhdtfhyyukyuoyuoiyouiouiotyjhfdhdfhfdhdrhfdhdfghdfghdfghdfghdfghfgh
            asdasdaasd34gerhdtfhyyukyuoyuoiyouiouiotyjhfdhdfhfdhdrhfdhdfghdfghdfghdfghdfghfgh
            asdasdaasd34gerhdtfhyyukyuoyuoiyouiouiotyjhfdhdfhfdhdrhfdhdfghdfghdfghdfghdfghfgh
            asdasdaasd34gerhdtfhyyukyuoyuoiyouiouiotyjhfdhdfhfdhdrhfdhdfghdfghdfghdfghdfghfgh
            asdasdaasd34gerhdtfhyyukyuoyuoiyouiouiotyjhfdhdfhfdhdrhfdhdfghdfghdfghdfghdfghfgh
            asdasdaasd34gerhdtfhyyukyuoyuoiyouiouiotyjhfdhdfhfdhdrhfdhdfghdfghdfghdfghdfghfgh
            asdasdaasd34gerhdtfhyyukyuoyuoiyouiouiotyjhfdhdfhfdhdrhfdhdfghdfghdfghdfghdfghfgh
            asdasdaasd34gerhdtfhyyukyuoyuoiyouiouiotyjhfdhdfhfdhdrhfdhdfghdfghdfghdfghdfghfgh
            asdasdaasd34gerhdtfhyyukyuoyuoiyouiouiotyjhfdhdfhfdhdrhfdhdfghdfghdfghdfghdfghfgh
            asdasdaasd34gerhdtfhyyukyuoyuoiyouiouiotyjhfdhdfhfdhdrhfdhdfghdfghdfghdfghdfghfgh
            asdasdaasd34gerhdtfhyyukyuoyuoiyouiouiotyjhfdhdfhfdhdrhfdhdfghdfghdfghdfghdfghfgh
            asdasdaasd34gerhdtfhyyukyuoyuoiyouiouiotyjhfdhdfhfdhdrhfdhdfghdfghdfghdfghdfghfgh
            asdasdaasd34gerhdtfhyyukyuoyuoiyouiouiotyjhfdhdfhfdhdrhfdhdfghdfghdfghdfghdfghfgh
            asdasdaasd34gerhdtfhyyukyuoyuoiyouiouiotyjhfdhdfhfdhdrhfdhdfghdfghdfghdfghdfghfgh

        ";
        $client = new \Trendi\Rpc\RpcClient("127.0.0.1", 9000,1);
        $data = $client->get("/rpc/index/index/kaihui", ["test"=>$str]);
        dump($data);

//
        $userDao = new \Trendi\Test\Dao\UserWxDao();
        $data = $userDao->test();
//
        $userDao = new \Trendi\Test\Dao\UserWxDao();
        $data = $userDao->test();
        dump($data);

        $redis = new \Trendi\Foundation\Storage\Redis();
        $redis->set("wang", "hello world");
        $data = $redis->get("wang");
        dump($data);

        cache()->set("wang", "wangkaihui");
        $data = cache()->get("wang");
        dump($data);

        $_SESSION["test"] = "trendi";
        dump($_SESSION["test"]);

        $response->view->test = "test";
        
        return $response->render("index/test");
    }

}