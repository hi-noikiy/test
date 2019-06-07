<?php

class Gearup_Sds_Model_Tracking extends Mage_Core_Model_Abstract {

    public function _construct(){
        parent::_construct();
        $this->_init("gearup_sds/tracking");
    }

    /*Ticket5492 - DXB Storage Manager - Report In/Out 
     Add cost and price field into download csv file*/
    public function downloadLastInbound($param){
        if ($param == '1'){
            $downloadLastInbound = Mage::getSingleton('core/session')->getLastInboundReport();
            $inbounds = Mage::getSingleton('core/session')->setInboundReport($downloadLastInbound);
        }
        if (Mage::getSingleton('core/session')->getInboundReport()){
            $inbounds = Mage::getSingleton('core/session')->getInboundReport();
            $path = Mage::getBaseDir() . '/media/dxbs/';
            $file = 'InboundStockReport - ' . date('dmY') . '.csv';
            $csv = new Varien_File_Csv();
            if (!file_exists($path)) {
                mkdir($path, 0777);
            }
            $i = 0;
            foreach ($inbounds as $inbound) {
                if (file_exists($path.$file)) {
                    $oldContent = $this->getCsvData($path.$file);
                } else {
                    $oldContent = '';
                }
                $head = array();
                $head['0'] = 'Product name';
                $head['1'] = 'Part number';
                $head['2'] = 'Inbound';
                $head['3'] = 'Price';
                $head['4'] = 'Cost';

                $completeData = array();
                $completeData['0'] = $inbound['product_name'];
                $completeData['1'] = $inbound['part_number'];
                $completeData['2'] = $inbound['inbound'];
                $completeData['3'] = $inbound['price'];
                $completeData['4'] = $inbound['cost'];
                if ($oldContent) {
                    $csvdata = $oldContent;
                } else {
                    $csvdata = array();
                    $csvdata[] = $head;
                }
                $csvdata[] = $completeData;
                $csv->saveData($path.$file, $csvdata);
                $i++;
            }

            if (file_exists($path.$file)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($path.$file).'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($path.$file));
                readfile($path.$file);
            }
            unlink($path.$file);
            Mage::getSingleton('core/session')->unsInboundReport();
        }
        return true;
    }

    public function getCsvData($file) {
        $csvObject = new Varien_File_Csv();
        try {
            return $csvObject->getData($file);
        } catch (Exception $e) {
            Mage::log('Csv: ' . $file . ' - getCsvData() error - '. $e->getMessage(), Zend_Log::ERR, 'system.log', true);
            return false;
        }

    }

    public function changeRedTrack ($productId, $type) {
        $product = Mage::getModel('catalog/product')->load($productId);
        if ($type == 1) {
            if (!$product->getSdsRed()) {
                $product->setSdsRed(1);
                $product->save();
            }
        } else if ($type == 2) {
            if ($product->getSdsRed()) {
                $product->setSdsRed(0);
                $product->save();
            }
        }
    }

}
