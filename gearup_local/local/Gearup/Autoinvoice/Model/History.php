<?php

class Gearup_Autoinvoice_Model_History extends Mage_Core_Model_Abstract {

    public function _construct(){
        parent::_construct();
        $this->_init("gearup_autoinvoice/history");
    }

    public function downloadLastChange(){
        if (Mage::getSingleton('core/session')->getInvoiceCReport()) {
            $invoices = Mage::getSingleton('core/session')->getInvoiceCReport();
            $path = Mage::getBaseDir() . '/media/dxbs/';
            $file = 'Invoice Change Status -' . date('dmY') . '.csv';
            $csv = new Varien_File_Csv();
            if (!file_exists($path)) {
                mkdir($path, 0777);
            }
            $i = 0;
            foreach ($invoices as $invoice) {
                if (file_exists($path.$file)) {
                    $oldContent = $this->getCsvData($path.$file);
                } else {
                    $oldContent = '';
                }
                $head = array();
                $head['0'] =' Order';
                $head['1'] = 'Invoice nr.';
                $head['2'] = 'Amount';
                $head['3'] = 'Date';

                $completeData = array();
                $completeData['0'] = $invoice['order'];
                $completeData['1'] = $invoice['invoicenr'];
                $completeData['2'] = $invoice['amount'];
                $completeData['3'] = $invoice['date'];
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
            Mage::getSingleton('core/session')->unsInvoiceCReport();
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
