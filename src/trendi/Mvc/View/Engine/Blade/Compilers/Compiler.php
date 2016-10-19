<?php

namespace Trendi\Mvc\View\Engine\Blade\Compilers;

use InvalidArgumentException;
use Trendi\Mvc\View\Engine\Blade\Filesystem;

abstract class Compiler
{
    /**
     * The Filesystem instance.
     *
     * @var \Trendi\Mvc\View\Engine\Blade\Filesystem
     */
    protected $files;

    /**
     * Get the cache path for the compiled views.
     *
     * @var string
     */
    protected $cachePath;

    /**
     * Create a new compiler instance.
     *
     * @param  \Trendi\Mvc\View\Engine\Blade\Filesystem  $files
     * @param  string  $cachePath
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($cachePath)
    {
        if (! $cachePath) {
            throw new InvalidArgumentException('Please provide a valid cache path.');
        }

        $this->files = new Filesystem();
        $this->cachePath = $cachePath;
    }

    /**
     * Get the path to the compiled version of a view.
     *
     * @param  string  $path
     * @return string
     */
    public function getCompiledPath($path)
    {
        return $this->cachePath.'/'.sha1($path).'.php';
    }

    /**
     * Determine if the view at the given path is expired.
     *
     * @param  string  $path
     * @return bool
     */
    public function isExpired($path)
    {
        $compiled = $this->getCompiledPath($path);

        // If the compiled file doesn't exist we will indicate that the view is expired
        // so that it can be re-compiled. Else, we will verify the last modification
        // of the views is less than the modification times of the compiled views.
        if (! $this->files->exists($compiled)) {
            return true;
        }

        $lastModified = $this->files->lastModified($path);

        return $lastModified >= $this->files->lastModified($compiled);
    }
}
