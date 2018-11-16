<?php
/**
 * MigrateCommand
 *
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */

namespace Trensy\Foundation\Command\Migrations;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class MigrateCommand extends \Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand
{
    public function execute(InputInterface $input, OutputInterface $output){
        $this->setMigrationConfiguration(Base::getConfig());
        parent::execute($input, $output);
    }
}