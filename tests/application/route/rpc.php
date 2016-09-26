<?php
Route::get("/rpc/index/index/{say}",
    ["uses" => "\\Trendi\\Test\\Rpc\\Index@index",
        "name" => "test", "middleware" => "111", "where" => ["say" => "\w+"]])->name("wangkaihui");