<?php
/**
 *  laravel blade 模板
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         1.0.7
 */
namespace Trensy\Mvc\View\Engine;

use Trensy\Config\Config;
use Trensy\Mvc\View\Engine\Blade\Engines\EngineResolver;
use Trensy\Mvc\View\ViewInterface;
use Trensy\Mvc\View\Engine\Blade\Compilers\BladeCompiler;
use Trensy\Mvc\View\Engine\Blade\Engines\CompilerEngine;
use Trensy\Mvc\View\Engine\Blade\Engines\PhpEngine;
use Trensy\Mvc\View\Engine\Blade\FileViewFinder;
use Trensy\Mvc\View\Engine\Blade\Factory;
use Trensy\Support\Dir;

class Blade implements ViewInterface
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
        require_once __DIR__."/Blade/helper.php";
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
        return $this->engineResolver->resolve('blade');
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
        foreach (['php', 'blade'] as $engine) {
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
     * @param  \Trensy\Mvc\View\Engine\Blade\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerPhpEngine($resolver)
    {
        $resolver->register('php', function () {
            return new PhpEngine;
        });
    }

    /**
     * Register the Blade engine implementation.
     *
     * @param  \Trensy\Mvc\View\Engine\Blade\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerBladeEngine($resolver)
    {
        $resolver->register('blade', function () {
            $cachePath =  $this->cachePath;
            $compiler = new BladeCompiler($cachePath);
            $compiler->directive('datetime', function($timestamp) {
                return preg_replace('/(\(\d+\))/', '<?php echo date("Y-m-d H:i:s", $1); ?>', $timestamp);
            });
            $engine = new CompilerEngine($compiler);
            return $engine;
        });
    }


}