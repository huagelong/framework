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


namespace Trensy\Mvc\View\Engine\Bladex;


use Trensy\Support\Dir;
use Trensy\Support\Tool;
use Trensy\Support\Log;

class Asset
{

    protected static $map = null;

    public function createDiffAsset($path, $targetPath, $exts, $viewPath, $staticDir = "static")
    {
//        var_dump(func_get_args());
        if (!is_dir($targetPath)) mkdir($targetPath, 0777, true);
        $targetPath = Dir::formatPath(realpath($targetPath));
        $path = Dir::formatPath(realpath($path));
        $viewPath = Dir::formatPath(realpath($viewPath));

        $map = $targetPath . "map.php";
        $this->getMap($map);
        //转移静态文件到发布目录
        Tool::xCopy($path, $targetPath);
        Log::sysinfo("copy static success!");
        $this->checkmtime($targetPath, [$targetPath, $path, $staticDir], $exts);
        Log::sysinfo("check file mtime success!");
        $releaseResult = [];
        $this->releaseMap($viewPath, $viewPath, $targetPath, $staticDir, $releaseResult);
        Log::sysinfo("collect file map success!");
        self::$map['view'] = $releaseResult;
        if (self::$map) {
            $this->savePHP($map, self::$map);
        }
        Log::sysinfo("static release success!");
    }

    protected function releaseMap($dir, $viewRoot, $targetPath, $staticDir, &$result)
    {
        if (!is_dir($dir)) {
            return false;
        }
        $d = dir($dir);
        while (false !== ($entry = $d->read())) {
            if ($entry != '.' && $entry != '..' && $entry != '.svn' && $entry != '.git') {
                if (is_dir($dir . '/' . $entry)) {
                    $this->releaseMap($dir . '/' . $entry, $viewRoot, $targetPath, $staticDir, $result);
                } else {
                    $filePath = realpath($dir . '/' . $entry);
                    $ext = substr($filePath, -11);
                    if (strtolower($ext) != ".bladex.php") continue;
                    $tmpPath = str_replace($viewRoot, "", $filePath);
                    $viewPathKey = substr($tmpPath, 0, -11);
                    $result[$viewPathKey] = $this->parseStatic($filePath, $viewRoot, $targetPath, $staticDir);
                }
            }
        }
        $d->close();
    }


    protected function parseStatic($filePath, $viewRoot, $targetPath, $staticDir)
    {
        $this->extendsMatch($filePath, $viewRoot, $extendsPath);
        $staticPathResult = [];
        if ($extendsPath) {
            foreach ($extendsPath as $v) {
                $tmp = $this->getStatic($v);
                if($tmp){
                    if(isset($tmp['js'])) $staticPathResult['js'] = array_merge(isset($staticPathResult['js'])?$staticPathResult['js']:[], $tmp['js']);
                    if(isset($tmp['css'])) $staticPathResult['css'] = array_merge(isset($staticPathResult['css'])?$staticPathResult['css']:[], $tmp['css']);
                }
                $jsTmp = $this->jsCode($v, $viewRoot, $targetPath, $staticDir);
                if ($jsTmp) {
                    $staticPathResult["js"][] = $jsTmp;
                }

                $cssTmp = $this->cssCode($v, $viewRoot, $targetPath, $staticDir);
                if ($cssTmp) {
                    $staticPathResult["css"][] = $cssTmp;
                }
            }
        }

        $selfStatic = $this->getStatic($filePath);
//        var_dump($selfStatic);
        if($selfStatic){
            if(isset($selfStatic['js']))  $staticPathResult['js'] = array_merge(isset($staticPathResult['js'])?$staticPathResult['js']:[], $selfStatic['js']);
            if(isset($selfStatic['css']))  $staticPathResult['css'] = array_merge(isset($staticPathResult['css'])?$staticPathResult['css']:[], $selfStatic['css']);
        }

//        var_dump($staticPathResult);
        $jsTmp = $this->jsCode($filePath, $viewRoot, $targetPath, $staticDir);
        if ($jsTmp) {
            $staticPathResult["js"][] = $jsTmp;
        }

        $cssTmp = $this->cssCode($filePath, $viewRoot, $targetPath, $staticDir);
        if ($cssTmp) {
            $staticPathResult["css"][] = $cssTmp;
        }


//        var_dump($staticPathResult);
        return $staticPathResult;
    }

    protected function jsCode($filePath, $viewRoot, $targetPath, $staticDir)
    {
//        var_dump($filePath);
        $result = [];
        $code = file_get_contents($filePath);
        preg_match_all("/@js(.*?)@endjs/is", $code, $match, PREG_SET_ORDER);
//        var_dump($match);
        foreach ($match as $v) {
            if ($v && isset($v[1]) && $v[1]) {
                $result[] = $v[1];
            }
        }

        if(!$result) return ;

        $jscode = implode("\n", $result);
        $hash = Tool::encode($jscode);
        $tmpName = str_replace("/", "_", substr(str_replace($viewRoot, "" ,$filePath), 0, -11));
        $file = "/{$staticDir}/glob/js/" .$tmpName . "_" . $hash . ".js";
        $realDir = $targetPath."glob/js/";
        if(!is_dir($realDir)) mkdir($realDir, 0777, true);
        $realPath = $realDir .$tmpName. "_" . $hash . ".js";
        file_put_contents($realPath, $jscode);
        return $file;
    }

    protected function cssCode($filePath, $viewRoot, $targetPath, $staticDir)
    {
        $result = [];
        $code = file_get_contents($filePath);
        preg_match_all("/@css(.*?)@endcss/is", $code, $match, PREG_SET_ORDER);
//        var_dump($match);
        foreach ($match as $v) {
            if ($v && isset($v[1]) && $v[1]) {
                $result[] = $v[1];
            }
        }

        if(!$result) return ;

        $cssCode = implode("\n", $result);
        $hash = Tool::encode($cssCode);
        $tmpName = str_replace("/", "_", substr(str_replace($viewRoot, "" ,$filePath), 0, -11));
        $file = "/{$staticDir}/glob/css/" .$tmpName . "_" . $hash . ".css";
        $realDir = $targetPath."glob/css/";
        if(!is_dir($realDir)) mkdir($realDir, 0777, true);
        $realPath = $realDir.$tmpName . "_" . $hash . ".css";
        file_put_contents($realPath, $cssCode);
        return $file;
    }

    protected function getStatic($filePath)
    {
        $result = [];
        $code = file_get_contents($filePath);
        preg_match_all("/@static\(([^\)]+)/i", $code, $match, PREG_SET_ORDER);
        foreach ($match as $v) {
            if ($v && isset($v[1]) && $v[1]) {
                $newFilePath = trim($v[1], "'");
                $newFilePath = trim($newFilePath, "\"");
                $ext = pathinfo($newFilePath, PATHINFO_EXTENSION);
                $result[$ext][] = $newFilePath;
            }
        }
        return $result;
    }

    protected function extendsMatch($filePath, $viewRoot, &$result)
    {
        $code = file_get_contents($filePath);
        preg_match_all("/@extends\(([^\)]+)/i", $code, $extendsMatch, PREG_SET_ORDER);
        $extendsMatch = current($extendsMatch);
        if ($extendsMatch && isset($extendsMatch[1]) && $extendsMatch[1]) {
            $newFilePath = trim($extendsMatch[1], "'");
            $newFilePath = trim($newFilePath, "\"");
            $newFilePath = $viewRoot . ltrim($newFilePath, "/");
            $result[] = $newFilePath;
            $this->extendsMatch($newFilePath, $viewRoot, $result);
        }
    }

    protected function savePHP($path, $data)
    {
        if (is_file($path)) {
            $code = include $path;
            $code = array_merge($code, $data);
        } else {
            $code = $data;
        }
        file_put_contents($path, "<?php " . PHP_EOL . " return " . var_export($code, true) . ";" . PHP_EOL);
    }

    protected function checkmtime($dir, $pathArr, $exts)
    {

        if (!is_dir($dir)) {
            return false;
        }
        $d = dir($dir);
        while (false !== ($entry = $d->read())) {
            if ($entry != '.' && $entry != '..' && $entry != '.svn' && $entry != '.git') {
                if (is_dir($dir . '/' . $entry)) {
                    $this->checkmtime($dir . '/' . $entry, $pathArr, $exts);
                } else {
                    list($targetPath, $path, $staticDir) = $pathArr;
                    $filePath = realpath($dir . '/' . $entry);
                    $replaceStr = "/{$staticDir}/";
                    $fileKey = str_replace($targetPath, $replaceStr, $filePath);
                    $fileFullPath = $path . str_replace($targetPath, "", $filePath);
                    $ext = pathinfo($fileFullPath, PATHINFO_EXTENSION);
                    if (in_array($ext, $exts)) {
                        if (!is_file($fileFullPath)) continue;
                        $mtime = filemtime($fileFullPath);
                        if (isset(self::$map['file'][$fileKey]['mtime'])) {
//                            echo $fileKey."|".self::$map['file'][$fileKey]['mtime']."|". $mtime."\n";
                            if (self::$map['file'][$fileKey]['mtime'] == $mtime) continue;
                            self::$map['file'][$fileKey]['mtime'] = $mtime;
                            $hash = base_convert($mtime, 16, 36);
                            $filePathArr = pathinfo($filePath);
                            $fileNewPath = $filePathArr['dirname'] . "/" . $filePathArr['filename'] . "_" . $hash . "." . $ext;
                            file_put_contents($fileNewPath, file_get_contents($filePath));
                            self::$map['file'][$fileKey]['realPath'] = str_replace($targetPath, $replaceStr, realpath($fileNewPath));
                        } else {
                            self::$map['file'][$fileKey]['mtime'] = $mtime;
                            self::$map['file'][$fileKey]['realPath'] = str_replace($targetPath, $replaceStr, realpath($filePath));
                        }
                    }
                }
            }
        }
        $d->close();
    }

    protected function getMap($targetPath)
    {
        if (self::$map) return;
        if (is_file($targetPath)) {
            self::$map = include_once $targetPath;
        }
    }

}