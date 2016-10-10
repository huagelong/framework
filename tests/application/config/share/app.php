<?php
return [
    "date_default_timezone_set"=>"Asia/Shanghai",
    "memory_limit"=>"1024M",
    "session"=>[
        "name"=>"TSESSIONID",
        "cache_expire"=>60*60*2,
        "path"=>"/",
        "domain"=>"",
        "secure"=>false,
        "httponly"=>true,
    ],
    "adapter"=>[
        "database"=>"pool",//pool or default
        "redis"=>"pool"
    ]
];