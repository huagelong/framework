<?php
/**
 * User: Peter Wang
 * Date: 16/9/23
 * Time: 下午1:33
 */
return [
    "servers"=>[
        "tcp://10.1.11.178:6379",
        "tcp://10.1.11.178:6384",
    ],
    "options"=>[
        'prefix'  => 'test',
        'cluster' => 'redis'
    ],
];