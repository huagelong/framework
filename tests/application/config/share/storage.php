<?php
/**
 * User: Peter Wang
 * Date: 16/10/9
 * Time: 下午12:45
 */
return [
    "pdo"=>[
        "type" => "mysql",
        "prefix" => "putao_",
        "master" =>[
            "host" => "10.1.11.166",
            "user" => "root",
            "port" => "3306",
            "password" => "123456",
            "db_name" => "putao_mall",
        ]
    ],
    "redis"=>[
        "servers"=>[
            "tcp://10.1.11.178:6379",
            "tcp://10.1.11.178:6384",
        ],
        "options"=>[
            'prefix'  => 'test',
            'cluster' => 'redis',
        ],
    ]
];