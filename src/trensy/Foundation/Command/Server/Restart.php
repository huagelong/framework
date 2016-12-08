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

namespace Trensy\Foundation\Command\Server;

use Trensy\Console\Input\InputInterface;
use Trensy\Console\Input\InputOption;
use Trensy\Console\Input\InputArgument;
use Trensy\Console\Output\OutputInterface;
use Trensy\Foundation\Command\Base;

class Restart extends Base
{
    protected function configure()
    {
        $this
            ->setName('server:restart')
            ->setDescription('restart the all server');
        $this->addOption('--daemonize', '-d', InputOption::VALUE_NONE, 'Is daemonize ?');
        $this->addOption('--option', '-o', InputOption::VALUE_OPTIONAL, 'diy server option ?');
    }

    protected function execute(InputInterface $input,OutputInterface $output)
    {
        ServerBase::operate("restart", $this, $input);
    }
}
