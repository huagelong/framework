<?php
/**
 *  laravel blade 模板
 * User: Peter Wang
 * Date: 16/9/13
 * Time: 下午2:01
 */
namespace Trendi\Mvc\View\Engine;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\ViewServiceProvider;
use Trendi\Mvc\View\ViewInterface;

class Blade implements ViewInterface
{
    protected static $instance = null;

    /**
     * Container instance.
     *
     * @var Container
     */
    protected $container;
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
        $this->container = new Container;
        $this->setupContainer();
        (new ViewServiceProvider($this->container))->register();
        $this->engineResolver = $this->container->make('view.engine.resolver');
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

    public function getView()
    {
        return $this->engineResolver->resolve('blade');
    }


    /**
     * Bind required instances for the service provider.
     */
    protected function setupContainer()
    {
        $this->container->bindIf('files', function () {
            return new Filesystem;
        }, true);
        $this->container->bindIf('events', function () {
            return new Dispatcher;
        }, true);
        $this->container->bindIf('config', function () {
            return [
                'view.paths' => (array)$this->viewPaths,
                'view.compiled' => $this->cachePath,
            ];
        }, true);
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
        return $this->container['view']->make($view, $data, [])->render();
    }

    /**
     * Get the compiler
     *
     * @return mixed
     */
    public function compiler()
    {
        $bladeEngine = $this->engineResolver->resolve('blade');
        return $bladeEngine->getCompiler();
    }

    /**
     * Pass any method to the view factory instance.
     *
     * @param  string $method
     * @param  array $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        return call_user_func_array([$this->container['view'], $method], $params);
    }

}