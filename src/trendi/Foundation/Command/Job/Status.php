<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Trendi\Foundation\Command\Job;

use Trendi\Console\Command\Command;
use Trendi\Console\Input\InputInterface;
use Trendi\Console\Output\OutputInterface;

class Status extends Command
{
    protected function configure()
    {
        $this
            ->setName('job:status')
            ->setDescription('show job server status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        JobBase::operate("status", $output, $input);
    }
}
