<?php
/**
 * Created by PhpStorm.
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Foundation\Command\Job;

use Trensy\Console\Input\InputInterface;
use Trensy\Console\Input\InputOption;
use Trensy\Console\Output\OutputInterface;
use Trensy\Foundation\Command\Base;

class Start extends Base
{
    protected function configure()
    {
        $this->setName('job:start')
            ->setDescription('start the job server ');
        $this->addOption('--daemonize', '-d', InputOption::VALUE_NONE, 'Is daemonize ?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        JobBase::operate("start", $output, $input);
    }
}
