<?php
/**
 *  job 服务初始化
 *
 * User: Peter Wang
 * Date: 16/9/13
 * Time: 下午3:38
 */

namespace Trensy\Foundation\Bootstrap;

use Trensy\Config\Config;
use Trensy\Job\Job;

class JobBootstrap extends Job
{
    public function __construct()
    {
        $config = Config::get("server.job");
        parent::__construct($config);
    }
}