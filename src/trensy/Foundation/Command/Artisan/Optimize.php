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

namespace Trensy\Foundation\Command\Artisan;

use Trensy\Console\Input\InputInterface;
use Trensy\Console\Input\InputOption;
use Trensy\Console\Output\OutputInterface;
use Trensy\Console\Input\InputArgument;
use Trensy\Foundation\Command\Base;
use Trensy\Support\Log;

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
