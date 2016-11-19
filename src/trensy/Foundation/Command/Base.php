<?php
/**
 * User: Peter Wang
 * Date: 16/10/19
 * Time: 下午3:06
 */

namespace Trensy\Foundation\Command;

use Trensy\Foundation\Bootstrap\Bootstrap;
use Trensy\Console\Command\Command;

class Base extends Command
{
  public function __construct()
  {
      parent::__construct();
      Bootstrap::getInstance(ROOT_PATH);
  }
}