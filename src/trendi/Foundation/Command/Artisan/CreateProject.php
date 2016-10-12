<?php
/**
 * Created by PhpStorm.
 * User: wangkaihui
 * Date: 16/7/22
 * Time: 下午6:27
 */

namespace Trendi\Foundation\Command\Artisan;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Trendi\Support\Log;

class CreateProject extends Command
{
    protected function configure()
    {
        $this->setName('create:project')
            ->setDescription('create project');
        $this->addArgument('name', InputArgument::REQUIRED, 'project name ?');
        $this->addArgument('dir', InputArgument::OPTIONAL, 'project dir ?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $path = $input->getArgument('dir');
        $path = $path ? $path : ROOT_PATH;

        if (!is_file($path . "/trendi")) {
            if (!is_dir($path)) {
                mkdir($path);
            }
        } else {
            Log::error("project existed!");
            return true;
        }

        $this->xCopy(__DIR__ . "/_dist/application", $path);
        chmod($path, 0777);
        chmod($path . "/storage", 0777);
        chmod($path . "/storage/tplcompile", 0777);

        //替换扩展名
        $this->changeExt($path);
        //名字替换
        $this->batchReplace($path, "/#Name#/", ucfirst($name));
        $composerJsonPath = $path . "/composer.json";
        if (is_file($composerJsonPath)) {
            unlink($path . "/composer.json.back");
            $code = file_get_contents($composerJsonPath);
            $json = json_decode($code,true);
            $json['name'] = $name;
            $json['autoload']['psr-4'][ucfirst($name)."\\"] = "src";
            $newCode = json_encode($json);
            file_put_contents($composerJsonPath, $newCode);
        } else {
            rename($path . "/composer.json.back", $path . "/composer.json");
        }
    }

    protected function xCopy($source, $destination, $child = 1)
    {
        if (!is_dir($source)) {
            return false;
        }
        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $handle = dir($source);
        while ($entry = $handle->read()) {
            if (($entry != ".") && ($entry != "..")) {
                if (is_dir($source . "/" . $entry)) {
                    if ($child) {
                        $this->xCopy($source . "/" . $entry, $destination . "/" . $entry, $child);
                    }
                } else {
                    copy($source . "/" . $entry, $destination . "/" . $entry);
                }
            }
        }
        return true;
    }

    protected function changeExt($source, $ext = 'dist')
    {
        if (!is_dir($source)) {
            return false;
        }
        $handle = dir($source);
        while ($entry = $handle->read()) {
            if (($entry != ".") && ($entry != "..")) {
                if (is_dir($source . "/" . $entry)) {
                    if($entry == 'vendor') continue;
                    $this->changeExt($source . "/" . $entry, $ext);
                } else {
                    $pathinfo = pathinfo($source . "/" . $entry);
                    if (isset($pathinfo['extension']) && ($pathinfo['extension'] == $ext)) {
                        rename($source . "/" . $entry, str_replace("." . $ext, "", $source . "/" . $entry));
                        Log::sysinfo(str_replace("." . $ext, "", $source . "/" . $entry) . " created");
                    }
                }
            }
        }
        return true;
    }

    protected function batchReplace($sourcePath, $reg, $replaceTo, $ext = "php,json,back")
    {
        if (!is_dir($sourcePath)) {
            return false;
        }

        $handle = dir($sourcePath);
        while ($entry = $handle->read()) {
            if (($entry != ".") && ($entry != "..")) {
                $tmpPath = $sourcePath . "/" . $entry;
                if (is_dir($tmpPath)) {
                    $this->batchReplace($tmpPath, $reg, $replaceTo, $ext);
                } else {
                    //开始替换
                    $pathinfo = pathinfo($tmpPath);
                    $extArr = explode(",", $ext);
                    if (isset($pathinfo['extension']) && in_array($pathinfo['extension'], $extArr)) {
                        $tmpData = file_get_contents($tmpPath);
                        $tmpData = preg_replace($reg, $replaceTo, $tmpData);
//                        Log::debug($tmpData);
                        file_put_contents($tmpPath, $tmpData);
                    }
                }
            }
        }
        return true;
    }
}
