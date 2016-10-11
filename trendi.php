#! /usr/bin/env php
<?php
/**
 * User: Peter Wang
 * Date: 16/10/11
 * Time: 下午6:57
 */
define("ROOT_PATH", __DIR__);
require_once __DIR__ . "/../autoload.php";
\Trendi\Foundation\Application::runCmd();