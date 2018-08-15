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

use Trensy\Console\Input\InputInterface;
use Trensy\Console\Output\OutputInterface;
use Trensy\Foundation\Command\Base;

class Stop extends Base
{
    protected function configure()
    {
        $this
            ->setName('jobd:stop')
            ->setDescription('stop the jobd server ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        JobdBase::operate("stop", $output, $input);
    }
}
