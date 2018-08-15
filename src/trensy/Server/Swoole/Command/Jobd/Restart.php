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
use Trensy\Console\Input\InputOption;
use Trensy\Console\Output\OutputInterface;
use Trensy\Foundation\Command\Base;

class Restart extends Base
{
    protected function configure()
    {
        $this
            ->setName('jobd:restart')
            ->setDescription('restart the jobd server');
        $this->addOption('--daemonize', '-d', InputOption::VALUE_NONE, 'Is daemonize ?');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        JobdBase::operate("restart", $output, $input);
    }
}
