<?php
/**
 * User: Peter Wang
 * Date: 16/9/23
 * Time: 下午5:06
 */

namespace Trendi\Cache;


interface CacheInterface
{

    public function set($key, $value);

    public function get($key, $default = null);

    public function del($key);
}