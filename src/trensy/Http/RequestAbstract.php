<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 2018/7/27
 * Time: 14:29
 */

namespace Trensy\Http;


abstract class RequestAbstract
{

    abstract function get();
    abstract function post();
    abstract function cookie();
    abstract function files();
    abstract function server();
    abstract function header();
    abstract function rawContent();
}