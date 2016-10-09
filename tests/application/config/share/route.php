<?php
return [
    "load_path"=>__DIR__."/../../route",
    "routes"=>[
        [
            "name"=>"site",
            "method"=>"get",
            "prefix"=>"",
            "domain"=>"",
            "middleware"=>"",
            "routes"=>[
                [
                    "method"=>"get",
                    "path"=>"/index/index/{say}",
                    "uses"=>"\\Trendi\\Test\\Controller\\Index@index",
                    "name"=>"test",
                    "middleware"=>"",
                    "where"=>["say" => "\w+"],
                ],
            ],

        ],
    ]
];