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
    protected  $viewRoot = null;
    protected  $viewCacheRoot = null;
    protected  $config=null;

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setViewRoot($viewRoot)
    {
        $this->viewRoot = $viewRoot;
    }

    public  function getViewRoot()
    {
        return $this->viewRoot;
    }

    public  function getViewCacheRoot()
    {
        return $this->viewCacheRoot;
    }

    public function setViewCacheRoot($viewCacheRoot)
    {
        $this->viewCacheRoot = $viewCacheRoot;
    }

    public static function setView($view)
    {
        self::$view = $view;
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
    public function render($viewPath, $assign = [])
    {
        $tpl = self::getView();

        if (!$tpl) {
            $tpl = View::getViewObj();
            self::setView($tpl);
        }

        $rootPath = $this->getViewRoot();
        $cacheRootPath = $this->getViewCacheRoot();
        $config = $this->getConfig();

        if (!$rootPath) {
            throw new InvalidArgumentException("view root path not found");
        }

        if (!$cacheRootPath) {
            throw new InvalidArgumentException("view cache/compile path not found");
        }

        $tpl->setViewRootPath($rootPath);
        $tpl->setCachePath($cacheRootPath);
        $tpl->setConfig($config);
        
        return $tpl->render($viewPath, $assign);
    }

    public function __destruct()
    {
    }
}