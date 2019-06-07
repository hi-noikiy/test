<?php


class Gearup_Cnetdescription_Adminhtml_CnetdescriptionController extends Mage_Adminhtml_Controller_Action
{
    public function _isAllowed() {
        return true;
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function compareAction()
    {
        $data = $_FILES['cnetdescription'];
        $path = Mage::getBaseDir() . DS . 'media/cnet';
        if (!Mage::helper('cnetdescription')->checkFileType($data['type'])) {
            $this->_getSession()->addError(Mage::helper('cnetdescription')->__('File upload not allow'));
            $this->_redirect('*/*/index');
            return false;
        }
        if (!file_exists($path)) {
            mkdir($path, 0777);
        }
        if (file_exists($path.'/'.$data['name'])) {
            unlink($path.'/'.$data['name']);
        }
        if (!file_exists($path.'/'.$data['name'])) {
            move_uploaded_file($data['tmp_name'], $path.'/'.$data['name']);
            $this->_redirect('*/*/result', array('file'=>base64_encode($data['name'])));
        }
    }

    public function resultAction()
    {
        $filepath = Mage::getBaseDir() . DS . 'media/cnet/' . base64_decode(Mage::app()->getRequest()->getParam('file'));
        if ($filepath) {
            
            $excelData = $this->getExcelData($filepath);

            foreach ($excelData as $data) {
                if ($data['EN+EN-GBR'] == "Y") {
                    $sku = $data['SKU'];
                    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                    if ($product) {
                        $product->setCnetDesc(1);
                        $product->save();
                    }
                }
            }
            $this->_getSession()->addSuccess(Mage::helper('cnetdescription')->__('File import sucessfully.'));
            $this->_redirect('*/*/index');
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
}