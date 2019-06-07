<?php
class Gearup_Sds_Block_Adminhtml_Stockcompare_Result extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getPath()
    {
        return Mage::getBaseDir() . DS . 'media/dxbs/compare';
    }

    public function getProductQTY($partnumber)
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('qty')
                ->addAttributeToSelect('part_nr');
        $collection->addFieldToFilter('part_nr', array('eq' => $partnumber));
        $product =  $collection->getFirstItem();
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        return $stock;
    }

    public function checkQtyMatch($pdata)
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*');
        $collection->joinField('qty','cataloginventory/stock_item','qty','product_id=entity_id','{{table}}.stock_id=1','left');
        $collection->addFieldToFilter('part_nr', array('eq' => trim($pdata['P/N'])));
        $collection->addFieldToFilter('dxbs', array('eq' => 1));
        $product =  $collection->getFirstItem();
        $collection->addFieldToFilter('qty', array('eq' => trim($pdata['Quantity'])));
        if ($collection->getSize()) {
            return array('class'=>'green', 'qty'=>$product->getQty(), 'status'=>1, 'sds'=>$product->getSameDayShipping(), 'pid'=>$product->getId());
        } else {
            return array('class'=>'red', 'qty'=>$product->getQty(), 'status'=>0, 'sds'=>$product->getSameDayShipping(), 'pid'=>$product->getId());
        }
    }

    public function getExcelData($file)
    {
        require_once Mage::getBaseDir('lib') . DS .'PHPExcel.php';
        require_once Mage::getBaseDir('lib') . DS .'PHPExcel/IOFactory.php';


        $inputFileName = $file;
        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($inputFileName);


        // for No header
//        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
//        $highestRow = $objWorksheet->getHighestRow();
//        $highestColumn = $objWorksheet->getHighestColumn();
//
//        $r = -1;
//        $namedDataArray = array();
//        for ($row = 1; $row <= $highestRow; ++$row) {
//            $dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
//            if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
//                ++$r;
//                $namedDataArray[$r] = $dataRow[$row];
//            }
//        }


        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();

        $headingsArray = $objWorksheet->rangeToArray('A1:'.$highestColumn.'1',null, true, true, true);
        $headingsArray = $headingsArray[1];

        $r = -1;
        $namedDataArray = array();
        for ($row = 2; $row <= $highestRow; ++$row) {
            $dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
            if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
                ++$r;
                foreach($headingsArray as $columnKey => $columnHeading) {
                    $namedDataArray[$r][preg_replace('/\s+/', '', $columnHeading)] = $dataRow[$row][$columnKey];
                }
            }
        }
        return $namedDataArray;
    }

    public function countDxbs()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*');
        $collection->addFieldToFilter('dxbs', array('eq' => 1));

        return $collection->getSize();
    }

    public function dxbsNotFile($pids)
    {
        $collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*');
        $collection->joinField('qty','cataloginventory/stock_item','qty','product_id=entity_id','{{table}}.stock_id=1','left');
        $collection->addFieldToFilter('dxbs', array('eq' => 1));
        if ($pids) {
            $collection->addFieldToFilter('entity_id', array('nin' => $pids));
        }
        return $collection;
    }
}
