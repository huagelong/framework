<?php
use Trendi\Mvc\View\Engine\Blade\Support\Arr;
/**
 * User: Peter Wang
 * Date: 16/10/8
 * Time: 下午3:36
 */
if (!function_exists('e')) {
    function e($value)
    {
        if(method_exists($value, "toHtml")) return $value->toHtml();

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }
}


if (! function_exists('array_except')) {
    /**
     * Get all of the given array except for a specified array of items.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    function array_except($array, $keys)
    {
        return Arr::except($array, $keys);
    }
}