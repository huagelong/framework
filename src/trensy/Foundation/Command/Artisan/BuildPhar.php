<?php
/**
 * Created by PhpStorm.
 * Trensy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      trensy, Inc.
 * @package         trensy/framework
 * @version         3.0.0
 */

namespace Trensy\Foundation\Command\Artisan;

use Trensy\Console\Input\InputArgument;
use Trensy\Console\Input\InputInterface;
use Trensy\Console\Input\InputOption;
use Trensy\Console\Output\OutputInterface;
use Trensy\Foundation\Command\Base;
use Trensy\Support\Dir;
use Trensy\Log;

class BuildPhar extends Base
{
    protected function configure()
    {
        $this->setName('phar:build')
            ->addArgument("file", InputArgument::REQUIRED, 'phar file path')
            ->setDescription('build  phar');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if (ini_get('phar.readonly') === '1') {
                throw new \Exception('Please set phar.readonly to Off');
            }

            $pharConfig = $this->config()->get("app.phar");
            if (!$pharConfig) {
                throw new \Exception('app.phar not config');
            }

            if (!isset($pharConfig['entry'])) {
                throw new \Exception('app.phar.entry not config');
            }

            if (!isset($pharConfig['directory'])) {
                throw new \Exception('app.phar.directory not config');
            }

            if (!isset($pharConfig['files'])) {
                throw new \Exception('app.phar.files not config');
            }

            $fileInput = $input->getArgument("file");
            $file = realpath(dirname($fileInput)) . "/" . basename($fileInput);

            $phar = new \Phar($file, \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::KEY_AS_FILENAME, basename($file));
            // 开始打包
            $phar->startBuffering();

            $files = $pharConfig['files'];
            if ($files) {
                foreach ($files as $v) {
                    $phar->addFile($v);
                }
            }

            $items = [];
            $directory = $pharConfig['directory'];
            if ($directory) {
                foreach ($directory as $dv) {
                    $this->getItem($dv, $items);
                }
                $phar->buildFromIterator(new \ArrayIterator($items));
            }

            $entry = $pharConfig['entry'];
            $phpCode = "<?php
Phar::mapPhar('" . basename($file) . "');
require 'phar://" . basename($file) . "/" . $entry . "';
__HALT_COMPILER();
?>";
            // 设置入口
            $phar->setStub($phpCode);
            $phar->stopBuffering();
            Log::show("Finished {$file}");
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    protected function getItem($dir, &$items)
    {
        $folder = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach($folder as $item) {
            //排除掉不需要的文件和目录
            if(strpos($item->getPathName(), '/.git/')) {
                continue;
            }
            $filename = pathinfo($item->getPathName(), PATHINFO_BASENAME);
            if(substr($filename, 0, 1) != '.') {
//                $items[substr($item->getPathName(), strlen($dir))] = $item->getPathName();
//                dump($item->getPathName());
                $items[$item->getPathName()] = $item->getPathName();
            }
        }
    }

}
