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

class Stop extends Command
{
    protected function configure()
    {
        $this
            ->setName('job:stop')
            ->setDescription('stop the job server ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        JobBase::operate("stop", $output, $input);
    }
}
