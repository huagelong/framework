<?php
/**
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */

namespace Trensy\Server;

use Trensy\Shortcut;
use Trensy\Foundation\Storage\Pdo;
use Trensy\Foundation\AnnotationLoadInterface;

abstract class TaskRunAbstract
{
    use Shortcut;

    abstract public function start($data);
    abstract public function finish($data);
}