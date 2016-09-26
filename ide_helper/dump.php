<?php
define('OUTPUT_FILE', __DIR__.'/ext.php');

function getFuncDef(array $funcs, $version)
{
    $all = '';
    foreach ($funcs as $k => $v) {
        $comment = '';
        $vp = array();
        $params = $v->getParameters();
        if ($params) {
            $comment = "/**\n";
            foreach ($params as $k1 => $v1) {
                if ($v1->isOptional()) {
                    $comment .= "* @param $" . $v1->name . "[optional]\n";
                    $vp[] = '$' . $v1->name . '=null';
                } else {
                    $comment .= "* @param $" . $v1->name . "[required]\n";
                    $vp[] = '$' . $v1->name;
                }
            }
            $comment .= "*/\n";
        }
        $comment .= sprintf("function %s(%s){}\n\n", $k, join(',', $vp));
        $all .= $comment;
    }
    return $all;
}

function getMethodsDef(array $methods, $version)
{
    $all = '';
    $sp4 = str_repeat(' ', 4);
    foreach ($methods as $k => $v) {

        $comment = '';
        $vp = array();

        $params = $v->getParameters();
        if ($params) {
            $comment = "$sp4/**\n";
            foreach ($params as $k1 => $v1) {
                if ($v1->isOptional()) {
                    $comment .= "$sp4* @param $" . $v1->name . "[optional]\n";
                    $vp[] = '$' . $v1->name . '=null';
                } else {
                    $comment .= "$sp4* @param $" . $v1->name . "[required]\n";
                    $vp[] = '$' . $v1->name;
                }
            }
            $comment .= "$sp4*/\n";
        }
        $modifiers = implode(
            ' ', Reflection::getModifierNames($v->getModifiers())
        );
        $comment .= sprintf(
            "$sp4%s function %s(%s){}\n\n", $modifiers, $v->name, join(',', $vp)
        );
        $all .= $comment;
    }
    return $all;
}

function export_ext($ext)
{
    $rf_ext = new ReflectionExtension($ext);
    $funcs = $rf_ext->getFunctions();
    $classes = $rf_ext->getClasses();
    $consts = $rf_ext->getConstants();
    $version = $rf_ext->getVersion();
    $defines = '';
    $sp4 = str_repeat(' ', 4);
    $fdefs = getFuncDef($funcs, $version);
    $class_def = '';
    foreach ($consts as $k => $v) {
        if (!is_numeric($v)) {
            $v = "'$v'";
        }
        $defines .= "define('$k',$v);\n";
    }
    foreach ($classes as $k => $v) {
        $prop_str = '';
        $props = $v->getProperties();
        array_walk(
            $props, function ($v, $k) {
            global $prop_str, $sp4;
            $modifiers = implode(
                ' ', Reflection::getModifierNames($v->getModifiers())
            );
            $prop_str .= "$sp4/**\n$sp4*@var $" . $v->name . " " . $v->class
                . "\n$sp4*/\n$sp4 $modifiers  $" . $v->name . ";\n\n";
        }
        );
        if ($v->getParentClass()) {
            $k .= ' extends ' . $v->getParentClass()->name;
        }
        $modifier = 'class';
        if ($v->isInterface()) {
            $modifier = 'interface';
        }
        $mdefs = getMethodsDef($v->getMethods(), $version);
        $class_def .= sprintf(
            "/**\n*@since %s\n*/\n%s %s{\n%s%s\n}\n", $version, $modifier, $k,
            $prop_str, $mdefs
        );
    }

    file_put_contents(
        OUTPUT_FILE, $defines . $fdefs . $class_def."\n",FILE_APPEND
    );
}
unlink(OUTPUT_FILE);
file_put_contents(
    OUTPUT_FILE, "<?php\n" ,FILE_APPEND
);
export_ext('swoole');
export_ext('runkit');
export_ext('memcached');
export_ext('hprose');
echo "swoole version: ".swoole_version()."\n";
echo "dump success.\n";

