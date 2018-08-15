<?php
/**
 * FileCache
 *
 * http://github.com/inouet/file-cache/
 *
 * A simple PHP class for caching data in the filesystem.
 *
 * License
 *   This software is released under the MIT License, see LICENSE.txt.
 *
 * @package FileCache
 * @author  Taiji Inoue <inudog@gmail.com>
 */
namespace Trensy\Storage\Cache\Adapter;

use Trensy\Foundation\Storage\Redis;
use Trensy\Storage\Cache\CacheInterface;
class FileCache implements CacheInterface
{
    /**
     * The root cache directory.
     * @var string
     */
    private $cache_dir = '/tmp/cache';

    private $name = "";
    /**
     * Creates a FileCache object
     *
     * @param array $options
     */
    public function __construct($name, $cacheDir)
    {
        $this->cache_dir = $cacheDir;
        $this->name = $name;
    }

    protected function parseId($id)
    {
        return strlen($id)>50 ? substr($id,0,50)."_".md5($id):$id;
    }

    protected function _clear($dir='') {
        $dir = $dir?$dir:$this->cache_dir;
        //先删除目录下的文件：
        if(!is_dir($dir)) return ;
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath= $dir."/".$file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    $this->_clear($fullpath);
                }
            }
        }
        closedir($dh);
        //删除当前文件夹：
        if(rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

    public function getAllKey()
    {
        $this->_getAllKey($keys);
        return $keys;
    }

    protected function _getAllKey(&$keys, $dir='')
    {
        $dir = $dir?$dir:$this->cache_dir;
        if(!is_dir($dir)) return ;
        //先删除目录下的文件：
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath= $dir."/".$file;
                if(!is_dir($fullpath)) {
                    $size =  filesize($fullpath);
                    $keys[$fullpath] = $size;
                } else {
                    $this->_getAllKey($keys, $fullpath);
                }
            }
        }
    }



    public function clear()
    {
        $this->_clear();
    }



    /**
     * Fetches an entry from the cache.
     *
     * @param string $id
     */
    public function get($id, $default = null)
    {
        $id = $this->parseId($id);
        $file_name = $this->getFileName($id);
        if (!is_file($file_name) || !is_readable($file_name)) {
            return $default;
        }
        $lines    = file($file_name);
        $lifetime = array_shift($lines);
        $lifetime = (int) trim($lifetime);
        if ($lifetime !== 0 && $lifetime < time()) {
            @unlink($file_name);
            return $default;
        }
        $serialized = join('', $lines);
        $data       = unserialize($serialized);
        return $data;
    }
    /**
     * Deletes a cache entry.
     *
     * @param string $id
     *
     * @return bool
     */
    public function del($id)
    {
        $file_name = $this->getFileName($id);
        return @unlink($file_name);
    }
    /**
     * Puts data into the cache.
     *
     * @param string $id
     * @param mixed  $data
     * @param int    $lifetime
     *
     * @return bool
     */
    public function set($id, $data, $lifetime = 3600)
    {
        $id = $this->parseId($id);
        $dir = $this->getDirectory($id);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return false;
            }
        }
        $file_name  = $this->getFileName($id);
        $lifetime   = time() + $lifetime;
        $serialized = serialize($data);
        $result     = file_put_contents($file_name, $lifetime . PHP_EOL . $serialized);

        if ($result === false) {
            return false;
        }
        return true;
    }

    public function addKeyTolist($id)
    {
        $obj = new Redis();
        $id = $this->parseId($id);
        $obj->sadd("trensy_".$this->name."_fileCache", [$id]);
        return ;
    }

    public function clearRedisList()
    {
        $obj = new Redis();
        while($key = $obj->spop("trensy_".$this->name."_fileCache"))
        {
            if($key){
                $this->del($key);
            }
        }
    }
    //------------------------------------------------
    // PRIVATE METHODS
    //------------------------------------------------
    /**
     * Fetches a directory to store the cache data
     *
     * @param string $id
     *
     * @return string
     */
    protected function getDirectory($id)
    {
        $hash = sha1($id, false);
        $dirs = array(
            $this->getCacheDirectory(),
            substr($hash, 0, 2),
            substr($hash, 2, 2)
        );
        return join(DIRECTORY_SEPARATOR, $dirs);
    }
    /**
     * Fetches a base directory to store the cache data
     *
     * @return string
     */
    protected function getCacheDirectory()
    {
        return $this->cache_dir;
    }
    /**
     * Fetches a file path of the cache data
     *
     * @param string $id
     *
     * @return string
     */
    protected function getFileName($id)
    {
        $directory = $this->getDirectory($id);
        $hash      = sha1($id, false);
        $file      = $directory . DIRECTORY_SEPARATOR . $hash . '.cache';
        return $file;
    }
}