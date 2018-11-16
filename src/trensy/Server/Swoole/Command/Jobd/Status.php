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

namespace Trensy\Server\Swoole\Command\Jobd;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trensy\Foundation\Command\Base;

class Status extends Base
{
    protected function configure()
    {
        $this
            ->setName('jobd:status')
            ->setDescription('show jobd server status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        JobdBase::operate("status", $output, $input);
    }
}
