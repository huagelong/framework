<?php
/**
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */

namespace Trensy\Server\Swoole\Command\Httpd;

use Trensy\Console\Input\InputInterface;
use Trensy\Console\Output\OutputInterface;
use Trensy\Foundation\Command\Base;

class Reload extends Base
{
    protected function configure()
    {
        $this
            ->setName('httpd:reload')
            ->setDescription('reload the http server ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        HttpdBase::operate("reload", $output, $input);
    }
}
