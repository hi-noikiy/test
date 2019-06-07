<?php

class Gearup_Shippingffdx_Model_History extends Mage_Core_Model_Abstract {

    public function _construct(){
        parent::_construct();
        $this->_init("gearup_shippingffdx/history");
    }

    public function downloadLastChange(){
        if (Mage::getSingleton('core/session')->getTrackCReport()) {
            $tracks = Mage::getSingleton('core/session')->getTrackCReport();
            $path = Mage::getBaseDir() . '/media/dxbs/';
            $file = 'Tracking Change Status -' . date('dmY') . '.csv';
            $csv = new Varien_File_Csv();
            if (!file_exists($path)) {
                mkdir($path, 0777);
            }
            $i = 0;
            foreach ($tracks as $track) {
                if (file_exists($path.$file)) {
                    $oldContent = $this->getCsvData($path.$file);
                } else {
                    $oldContent = '';
                }
                $head = array();
                $head['0'] =' Order';
                $head['1'] = 'Track Number';
                $head['2'] = 'Weight';
                $head['3'] = 'Shipping';
                $head['4'] = 'Date';

                $completeData = array();
                $completeData['0'] = $track['order'];
                $completeData['1'] = $track['track'];
                $completeData['2'] = $track['weight'];
                $completeData['3'] = $track['shippingamount'];
                $completeData['4'] = $track['date'];
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
            Mage::getSingleton('core/session')->unsTrackCReport();
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
}
