<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Trendi\Foundation\Command\Job;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
