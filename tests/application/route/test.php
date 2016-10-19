<?php
Route::get("/index/test", ["uses" => "\\Trendi\\Test\\Controller\\Index@test",])->name("wangtest");