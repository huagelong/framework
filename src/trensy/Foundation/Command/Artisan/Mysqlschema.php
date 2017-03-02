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
use Trensy\Foundation\Command\Base;
use Trensy\Foundation\Storage\Pdo;
use Trensy\Support\Dir;
use Trensy\Support\Log;

class Mysqlschema extends Base
{
    private $db = null;

    protected function configure()
    {
        $this->setName('mysqlschema')
            ->addArgument("output", InputArgument::REQUIRED, "output path")
            ->addArgument("config", InputArgument::OPTIONAL, "sync database config")
            ->setDescription('export database schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $storageConfig = config()->get("storage.server.pdo");
        $inputConfig = $input->getArgument("config");
        $inputConfig = $inputConfig ? $inputConfig : $storageConfig;

        $output = $input->getArgument("output");
        $this->db = new Pdo($inputConfig);
        $dbName = $inputConfig['master']['db_name'];
        $this->export($dbName, $output);

        Log::sysinfo("export completed!");
    }


    protected function export($dbName, $output)
    {
        $tables = $this->tables($dbName);
        if (!$tables) {
            Log::error("not databases tables exist!");
            return;
        }
        $result = [];
        $fileName = $dbName . " schema." . date('YmdHis');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setTitle($fileName);
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
        $sheetObj = $objPHPExcel->setActiveSheetIndex(0);
        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'outline' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,   //设置border样式
                    //'style' => PHPExcel_Style_Border::BORDER_THICK,  另一种样式
                    'color' => array('argb' => '000000'),          //设置border颜色
                ),
                'inside' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'
                    )
                ),

            ),
        );

        $sheetObj->getColumnDimension('A')->setAutoSize(true);   //内容自适应
        $sheetObj->getColumnDimension('B')->setAutoSize(true);   //内容自适应
        $sheetObj->getColumnDimension('C')->setAutoSize(true);   //内容自适应
        $sheetObj->getColumnDimension('D')->setAutoSize(true);   //内容自适应
        $sheetObj->getColumnDimension('E')->setAutoSize(true);   //内容自适应
        $sheetObj->getColumnDimension('F')->setAutoSize(true);   //内容自适应

        $i = 1;
        foreach ($tables as $k => $v) {
            $n = $i;
            $table = $v['table_name'];
            $engine = $v['engine'];

            $sheetObj = $sheetObj->setCellValue("A" . $i, "表名");
            $sheetObj->getStyle("A" . $i)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_PATTERN_MEDIUMGRAY);
            $sheetObj->getStyle("A" . $i)->getFill()->getStartColor()->setARGB('aaaaaa');
            $sheetObj->getStyle("A" . $i)->getFont()->getColor()->setARGB(\PHPExcel_Style_Color::COLOR_WHITE);
            $sheetObj->getStyle("A" . $i)->getFont()->setSize(14);
            $sheetObj = $sheetObj->setCellValue("B" . $i, $table);
            $sheetObj->getStyle("B" . $i)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $sheetObj->getStyle("B" . $i)->getFill()->getStartColor()->setARGB('dddddd');
            $sheetObj->mergeCells("B" . $i.":"."F" . $i);

            $i++;
            $sheetObj = $sheetObj->setCellValue("A" . $i, "引擎");
            $sheetObj->getStyle("A" . $i)->getFont()->setSize(14);
            $sheetObj->getStyle("A" . $i)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_PATTERN_MEDIUMGRAY);
            $sheetObj->getStyle("A" . $i)->getFill()->getStartColor()->setARGB('aaaaaa');
            $sheetObj->getStyle("A" . $i)->getFont()->getColor()->setARGB(\PHPExcel_Style_Color::COLOR_WHITE);
            $sheetObj = $sheetObj->setCellValue("B" . $i, $engine);
            $sheetObj->getStyle("B" . $i)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
            $sheetObj->getStyle("B" . $i)->getFill()->getStartColor()->setARGB('dddddd');
            $sheetObj->mergeCells("B" . $i.":"."F" . $i);

            $i++;
            $sheetObj = $sheetObj->setCellValue("A" . $i, "参数名");
            $sheetObj = $sheetObj->setCellValue("B" . $i, "类型");
            $sheetObj = $sheetObj->setCellValue("C" . $i, "默认值");
            $sheetObj = $sheetObj->setCellValue("D" . $i, "自增");
            $sheetObj = $sheetObj->setCellValue("E" . $i, "为空");
            $sheetObj = $sheetObj->setCellValue("F" . $i, "说明");
            $sheetObj->getStyle("A" . $i . ":" . "F" . $i)->getFont()->setSize(14);
            $sheetObj->getStyle("A" . $i . ":" . "F" . $i)->getFont()->getColor()->setARGB(\PHPExcel_Style_Color::COLOR_WHITE);
            $sheetObj->getStyle("A" . $i . ":" . "F" . $i)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_PATTERN_MEDIUMGRAY);
            $sheetObj->getStyle("A" . $i . ":" . "F" . $i)->getFill()->getStartColor()->setARGB('aaaaaa');
            $fields = $this->fields($table);

            if ($fields) {
                $m=0;
                foreach ($fields as $fv) {
                    $i++;
                    $sheetObj = $sheetObj->setCellValue("A" . $i, $fv['field']);
                    $sheetObj = $sheetObj->setCellValue("B" . $i, $fv['full_type']);
                    $sheetObj = $sheetObj->setCellValue("C" . $i, $fv['default']);
                    $sheetObj = $sheetObj->setCellValue("D" . $i, $fv['auto_increment']?" Y":" N");
                    $sheetObj = $sheetObj->setCellValue("E" . $i, $fv['null']?" Y":" N");
                    $sheetObj = $sheetObj->setCellValue("F" . $i, $fv['comment']);
                    if($m%2){
                        $sheetObj->getStyle("A" . $i . ":" . "F" . $i)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                        $sheetObj->getStyle("A" . $i . ":" . "F" . $i)->getFill()->getStartColor()->setARGB('dddddd');
                    }
                    $m++;
                }
            }
            $indexes = $this->indexes($table);

            if ($indexes) {
                $i++;
                $sheetObj = $sheetObj->setCellValue("A" . $i, "索引名称");
                $sheetObj = $sheetObj->setCellValue("B" . $i, "类型");
                $sheetObj = $sheetObj->setCellValue("C" . $i, "字段");
                $sheetObj = $sheetObj->setCellValue("D" . $i, "说明");
                $sheetObj->getStyle("A" . $i . ":" . "F" . $i)->getFont()->setSize(14);
                $sheetObj->getStyle("A" . $i . ":" . "F" . $i)->getFont()->getColor()->setARGB(\PHPExcel_Style_Color::COLOR_WHITE);
                $sheetObj->getStyle("A" . $i . ":" . "F" . $i)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_PATTERN_MEDIUMGRAY);
                $sheetObj->getStyle("A" . $i . ":" . "F" . $i)->getFill()->getStartColor()->setARGB('aaaaaa');
                $sheetObj->mergeCells("D" . $i.":"."F" . $i);
                $m=0;
                foreach ($indexes as $ik=>$iv) {
                    $i++;
                    $sheetObj = $sheetObj->setCellValue("A" . $i, $ik);
                    $sheetObj = $sheetObj->setCellValue("B" . $i, $iv['type']);
                    $sheetObj = $sheetObj->setCellValue("C" . $i, implode(",", $iv['columns']));
                    $sheetObj = $sheetObj->setCellValue("D" . $i, implode(",",$iv['descs'])?implode(",",$iv['descs']):"");
                    if($m%2){
                        $sheetObj->getStyle("A" . $i . ":" . "F" . $i)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                        $sheetObj->getStyle("A" . $i . ":" . "F" . $i)->getFill()->getStartColor()->setARGB('dddddd');
                    }
                    $sheetObj->mergeCells("D" . $i.":"."F" . $i);
                    $m++;
                }
            }

            $sheetObj->getStyle("A{$n}:F{$i}")->applyFromArray($styleThinBlackBorderOutline);

            $i++;
            $sheetObj = $sheetObj->setCellValue("A".$i, " ");
            $i++;
            $sheetObj = $sheetObj->setCellValue("A".$i, " ");
            $i++;
            $sheetObj = $sheetObj->setCellValue("A".$i, " ");

            $i++;
        }


        $output = Dir::formatPath($output);
        $outPutObj = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $outPutObj->save($output . $fileName . ".xlsx");
    }

    protected function fields($table)
    {
        $return = array();
        $data = $this->getRows("SHOW FULL COLUMNS FROM `$table`");
        foreach ($data as $row) {
            preg_match('~^([^( ]+)(?:\\((.+)\\))?( unsigned)?( zerofill)?$~', $row["Type"], $match);
            $return[$row["Field"]] = array(
                "field" => $row["Field"],
                "full_type" => $row["Type"],
                "type" => $match[1],
                "length" => isset($match[2]) ? $match[2] : null,
                "unsigned" => isset($match[4]) ? ltrim($match[3] . $match[4]) : '',
                "default" => ($row["Default"] != "" || preg_match("~char|set~", $match[1]) ? $row["Default"] : null),
                "null" => ($row["Null"] == "YES"),
                "auto_increment" => ($row["Extra"] == "auto_increment"),
                "on_update" => (preg_match('~^on update (.+)~i', $row["Extra"], $match) ? $match[1] : ""), //! available since MySQL 5.1.23
                "collation" => $row["Collation"],
                "privileges" => array_flip(preg_split('~, *~', $row["Privileges"])),
                "comment" => $row["Comment"],
                "primary" => ($row["Key"] == "PRI"),
            );
        }
        return $return;
    }

    protected function indexes($table)
    {
        $return = array();
        $data = $this->getRows("SHOW INDEX FROM `$table`");
        foreach ($data as $row) {
            $return[$row["Key_name"]]["type"] = ($row["Key_name"] == "PRIMARY" ? "PRIMARY" : ($row["Index_type"] == "FULLTEXT" ? "FULLTEXT" : ($row["Non_unique"] ? "INDEX" : "UNIQUE")));
            $return[$row["Key_name"]]["columns"][] = $row["Column_name"];
            $return[$row["Key_name"]]["lengths"][] = $row["Sub_part"];
            $return[$row["Key_name"]]["descs"][] = null;
        }
        return $return;
    }

    protected function tables($dbname)
    {
        $return = array();
        $data = $this->getRows("SELECT table_name,engine FROM information_schema.tables WHERE table_schema = '$dbname'");
        foreach ($data as $row) {
            $return[] = $row;
        }
        return $return;
    }

    protected function getRows($sql)
    {
        return $this->db->selectAll($sql);
    }

}