<?php
/**
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */

namespace Trensy\Mvc\View;


interface ViewInterface
{
    public static function getInstance();

    public function setViewRootPath($path);

    public function setCachePath($path);

    public function render($path, $assign);

    public function getView();
}