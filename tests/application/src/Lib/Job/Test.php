<?php
namespace Trendi\Test\Lib\Job;
/**
 * User: Peter Wang
 * Date: 16/9/26
 * Time: 下午2:16
 */
class Test
{

    private $jobParam = null;

    public function __construct($jobParam)
    {
        $this->jobParam = $jobParam;
    }

    public function perform()
    {
        echo date('Y-m-d H:i:s')."-".$this->jobParam."\n";
    }

}