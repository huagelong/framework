<?php
/**
 *  bladex 模板
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */
namespace Trensy\Mvc\View\Engine;

use Trensy\Config;
use Trensy\Di;
use Trensy\Log;
use Trensy\Mvc\View\Engine\Bladex\Engines\EngineResolver;
use Trensy\Mvc\View\ViewInterface;
use Trensy\Mvc\View\Engine\Bladex\Compilers\BladexCompiler;
use Trensy\Mvc\View\Engine\Bladex\Engines\CompilerEngine;
use Trensy\Mvc\View\Engine\Bladex\Engines\PhpEngine;
use Trensy\Mvc\View\Engine\Bladex\FileViewFinder;
use Trensy\Mvc\View\Engine\Bladex\Factory;
use Trensy\Support\Dir;

class Bladex implements ViewInterface
{
    protected static $instance = null;
    protected $config = null;

    /**
     * Engine Resolver
     *
     * @var
     */
    protected $engineResolver;

    /**
     * Constructor.
     *
     */
    public function __construct()
    {

    }

    public static function getInstance()
    {
        if (self::$instance) return self::$instance;
        return self::$instance = new self();
    }

    public function setViewRootPath($path)
    {
        $this->viewPaths = $path;
    }

    public function setCachePath($path)
    {
        $this->cachePath = $path;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getView()
    {
        return $this->engineResolver->resolve('bladex');
    }



    /**
     * Render shortcut.
     *
     * @param  string $view
     * @param  array $data
     *
     * @return string
     */
    public function render($view, $data = [])
    {
        $path = $this->viewPaths;
        $resolver = new EngineResolver();
        foreach (['php', 'bladex'] as $engine) {
            $this->{'register'.ucfirst($engine).'Engine'}($resolver);
        }
        $path = is_array($path)?$path:[$path];
        $finder = new FileViewFinder($path);

        $factory = new Factory($resolver, $finder, $this->config);

        $result= $factory->make($view, $data, [])->render();
        
        return $result;
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param  \Trensy\Mvc\View\Engine\Bladex\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerPhpEngine($resolver)
    {
        $resolver->register('php', function () {
            return new PhpEngine;
        });
    }

    /**
     * Register the Bladex engine implementation.
     *
     * @param  \Trensy\Mvc\View\Engine\Bladex\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerBladexEngine($resolver)
    {
        $resolver->register('bladex', function () {
            $cachePath =  $this->cachePath;
            $compiler = new BladexCompiler($cachePath);
            
            list($map, $bladexEx) = $this->config;
            
            if($bladexEx){
                foreach ($bladexEx as $k=>$class){
                    $compiler->directive($k, function($param) use ($class){
                        $obj = Di::get($class);
                        return $obj->perform($param);
                    });
                }
            }
            $engine = new CompilerEngine($compiler);
            return $engine;
        });
    }


}