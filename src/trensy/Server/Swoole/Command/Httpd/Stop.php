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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trensy\Foundation\Command\Base;

class Stop extends Base
{
    protected function configure()
    {
        $this
            ->setName('httpd:stop')
            ->setDescription('stop the http server ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        HttpdBase::operate("stop", $output, $input);
    }
}
