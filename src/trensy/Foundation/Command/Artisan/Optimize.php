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
 * @version         3.0.0
 */

namespace Trensy\Foundation\Command\Artisan;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Trensy\Foundation\Command\Base;
use Trensy\Log;

class Optimize extends Base
{
    protected function configure()
    {
        $this->setName('optimize')
            ->setDescription('optimize project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if($this->checkCmd("composer")){
            $cmdStr = "composer dump-autoload --optimize";
            exec($cmdStr);
            Log::sysinfo(" 'composer dump-autoload --optimize' run success! ");
        }
    }

    protected function checkCmd($cmd)
    {
        $cmdStr = "command -v ".$cmd;
        exec($cmdStr, $check);
        if(!$check){
            return false;
        }else{
            return current($check);
        }
    }

}
