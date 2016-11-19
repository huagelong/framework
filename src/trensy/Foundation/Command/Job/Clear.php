<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Trensy\Foundation\Command\Job;

use Trensy\Console\Input\InputInterface;
use Trensy\Console\Output\OutputInterface;
use Trensy\Foundation\Command\Base;

class Clear extends Base
{
    protected function configure()
    {
        $this
            ->setName('job:clear')
            ->setDescription('clear the job data ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        JobBase::operate("clear", $output, $input);
    }
}
