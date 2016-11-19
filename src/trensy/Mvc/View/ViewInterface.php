<?php
/**
 * User: Peter Wang
 * Date: 16/9/13
 * Time: 上午9:48
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