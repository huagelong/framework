<?php
return [
    "date_default_timezone_set"=>"Asia/Shanghai",
    "memory_limit"=>"1024M",
    "session"=>[
        "name"=>"TSESSIONID",
        "cache_expire"=>60*60*2,
        "path"=>"/",
        "domain"=>"test.app",
        "secure"=>false,
        "httponly"=>true,
    ],
];