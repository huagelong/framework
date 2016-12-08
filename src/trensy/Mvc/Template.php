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

namespace Trensy\Mvc;

use Trensy\Mvc\Exception\InvalidArgumentException;
use Trensy\Mvc\View\View;

class Template
{
    /**
     * @var \Trensy\Mvc\View\View
     */
    protected static $view = null;
    protected static $viewRoot = null;
    protected static $viewCacheRoot = null;
    protected static $engine = View::DEFAULT_ENGINE;


    public static function setViewRoot($viewRoot)
    {
        self::$viewRoot = $viewRoot;
    }

    public static function getViewRoot()
    {
        return self::$viewRoot;
    }

    public static function getViewCacheRoot()
    {
        return self::$viewCacheRoot;
    }

    public static function setViewCacheRoot($viewCacheRoot)
    {
        self::$viewCacheRoot = $viewCacheRoot;
    }

    public static function setView($view)
    {
        self::$view = $view;
    }

    public static function setEngine($engine)
    {
        self::$engine = $engine;
    }

    public static function getEngine()
    {
        return self::$engine;
    }

    public static function getView()
    {
        return self::$view;
    }


    /**
     * 模板处理
     * @param $viewPath
     * @param array $assign
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function render($viewPath, $assign = [])
    {
        $tpl = self::getView();

        if (!$tpl) {
            View::setEngine(self::getEngine());
            $tpl = View::getViewObj();
            self::setView($tpl);
        }

        $rootPath = self::getViewRoot();
        $cacheRootPath = self::getViewCacheRoot();

        if (!$rootPath) {
            throw new InvalidArgumentException("view root path not found");
        }

        if (!$cacheRootPath) {
            throw new InvalidArgumentException("view cache/compile path not found");
        }

        $tpl->setViewRootPath($rootPath);
        $tpl->setCachePath($cacheRootPath);
        return $tpl->render($viewPath, $assign);
    }

    public function __destruct()
    {
    }
}