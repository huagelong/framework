<?php
/**
 *  job 服务初始化
 *
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
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