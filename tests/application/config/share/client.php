<?php
/**
 * User: Peter Wang
 * Date: 16/9/22
 * Time: 上午11:09
 */
return [
    "rpc"=>[
        "host" => "127.0.0.1",
        "port" => "9000",
        "timeout" => 3,
        "serialization" => 1,
        //以下配置直接复制，无需改动
        'open_length_check' => 1,
        'package_length_type' => 'N',
        'package_length_offset' => 0,
        'package_body_offset' => 4,
        'package_max_length' => 2000000000,
       
    ],
    "pool"=>[
        "host" => "127.0.0.1",
        "port" => "9001",
        "timeout" => 3,
        "serialization" => 1,
        "alway_keep"=>true,
        "pdo"=>[
            "prefix"=>"putao_"
        ],
    ],
];