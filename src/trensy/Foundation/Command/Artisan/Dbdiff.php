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

namespace Trensy\Foundation\Command\Artisan;

use Trensy\Console\Input\InputArgument;
use Trensy\Console\Input\InputInterface;
use Trensy\Console\Output\OutputInterface;
use Trensy\Console\Input\InputOption;
use Trensy\Foundation\Command\Base;
use Trensy\Support\Dir;
use Trensy\Support\Log;

class Dbdiff extends Base
{
    protected function configure()
    {
        $this->setName('db:diff')
            ->addArgument("type", InputArgument::OPTIONAL, 'schema or data or all - Specifies the type of diff to do either on the schema, data or both. schema is the default', 'schema')
            ->setDescription('db diff');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if (!$this->checkCmd("composer")) {
                throw  new \Exception("composer command not found");
            }

            $file = ROOT_PATH . "/vendor/bin/dbdiff";

            if(!is_file($file)){
                throw  new \Exception("dbdiff not found, pls run `composer require dbdiff/dbdiff:@dev` ");
            }

            if(!$this->config()->get("dbdiff")){
                throw  new \Exception("config dbdiff not found");
            }

            $server1 = $this->config()->get("dbdiff.server1");
            $dbServer1Str = $server1['user'] . ":" . $server1['password'] . "@" . $server1['host'] . ":" . $server1['port'];

            $server2 = $this->config()->get("dbdiff.server2");
            $dbServer2Str = $server2['user'] . ":" . $server2['password'] . "@" . $server2['host'] . ":" . $server2['port'];

            $type = $input->getArgument("type");
            $type = $type ? $type : "schema";

            if (!in_array($type, ['schema', 'data', 'all'])) {
                throw  new \Exception("type must 'schema or data or all '");
            }

            $nocomments = $this->config()->get("dbdiff.nocomments");
            $output = $this->config()->get("dbdiff.output");

            $options = [];
            $options['--server1'] = $dbServer1Str;
            $options['--server2'] = $dbServer2Str;
            $options['--type'] = $type;
            $options['--nocomments'] = $nocomments;

            $optionStr = "";
            foreach ($options as $k => $v) {
                $optionStr .= " " . $k . "=" . $v;
            }

            $dbStr = "server1." . $server1['dbname'] . ":server2." . $server2['dbname'];

            $includeArr = ["up", "down"];

            $now = date('YmdHi');
            $output = Dir::formatPath($output);
            foreach ($includeArr as $v) {
                $inner = [];
                $inner['--output'] = $output . $now . "_" . $v . ".sql";
                $inner['--include'] = $v;
                $innerStr = "";
                foreach ($inner as $k => $iv) {
                    $innerStr .= " " . $k . "=" . $iv;
                }

                $cmd = $this->whichBin("php") . " " . $file . " " . $optionStr . " " . $innerStr . " " . $dbStr;
                $this->exec($cmd);
                Log::show("RUN [" . $cmd . "] Success!");
            }

            $cmd = $this->whichBin("php") ." ". ROOT_PATH."/trensy db:sync --config storage.server.diff";
            $this->exec($cmd);
            Log::show("RUN [" . $cmd . "] Success!");
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    protected function checkCmd($cmd)
    {
        $cmdStr = "command -v ".$cmd;
        exec($cmdStr, $check);
        if(!$check){
            return false;
        }else{
            return current($check);
        }
    }

    /**
     * 查询命令全部路径
     * @param $str
     * @return bool
     */
    protected function whichBin($str){
        exec("which {$str}", $result);
        if($result) return current($result);
        return false;
    }

    protected function exec($command){
        exec($command , $outRs, $rs);
        return $outRs?$outRs:false;
    }

}
