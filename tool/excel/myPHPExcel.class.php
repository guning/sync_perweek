<?php
/**
 * todo:封装一个处理PHPExcel生成文件的类
 * User: guning
 * DateTime: 2017-7-28 11:49
 */
include "PHPExcel.php";
class myPHPExcel {
    protected $objPHPExcel;
    public function __construct(){
        $this->objPHPExcel = new PHPExcel();
    }

    /**
     * 设置表格单元值
     * @param $data : array()格式必须为array(int第几行=>array(array(int第几列=>列值)))，第一行的列值最好设置为string列名
     */
    public function saveData($data, $sheet=0, $sheetName) {
        if ($sheet == 0) {
            $this->objPHPExcel->setActiveSheetIndex(0);
        } else {
            $this->objPHPExcel->createSheet();
            $this->objPHPExcel->setActiveSheetIndex($sheet);
        }
        if (!empty($sheetName)) {
            $this->objPHPExcel->getActiveSheet()->setTitle($sheetName);
        }
        foreach ($data as $rowNum => $rowVal) {
            foreach ($rowVal as $colNum => $colVal) {
                $this->objPHPExcel->getActiveSheet()->setCellValue(chr($colNum + 65).$rowNum, $colVal);
            }
        }
    }

    /**
     * 保存为excel
     * @param $path 保存路径
     */
    public function toExcel($path) {
        $objWriter = new \PHPExcel_Writer_Excel2007($this->objPHPExcel);
        $objWriter->save($path . ".xlsx");
    }

    /**
     * 返回PHPExcel用于其他设置
     * @return PHPExcel
     */
    public function getObjPHPExcel(){
        return $this->objPHPExcel;
    }
    public function setObjPHPExcel($objPHPExcel){
        $this->objPHPExcel = $objPHPExcel;
    }
}