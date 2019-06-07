<?php

class Gearup_OrderManager_Adminhtml_PeriodController extends Mage_Adminhtml_Controller_Action
{
    public function exportperiodAction()
    {
        $periodId = Mage::app()->getRequest()->getParam('period_id');
        if ($periodId) {
            $period = Mage::getModel('hordermanager/period')->load($periodId);
            $path = Mage::getBaseDir() . '/media/dxbs/period/' . $period->getData('custom_period_id') . '/';
            $file = 'Stock_Outbound_' . Mage::getModel('core/date')->date('dmY') . '.xls';

            //Mage::helper('gearup_ordermanager')->exportperiodCSVsecond($periodId);
            Mage::helper('gearup_ordermanager')->exportSDSperiodXls($periodId);
            if (file_exists($path.$file)) {
                $this->_prepareDownloadResponse($file, array('type' => 'filename', 'value' => $path.$file, 'rm' => true));
            }
            //unlink($path.$file);
        }

        //$this->_redirect('hordermanager/adminhtml_period/view', array('period_id'=> $periodId));
    }

    public function exportperiodxlsAction()
    {
        $periodId = Mage::app()->getRequest()->getParam('period_id');
        if ($periodId) {
            $period = Mage::getModel('hordermanager/period')->load($periodId);
            $path = Mage::getBaseDir() . '/media/dxbs/period/' . $period->getData('custom_period_id') . '/';
            $xlsFile = 'orderperiod-' . $period->getData('custom_period_id') . '.xls';

            Mage::helper('gearup_ordermanager')->exportperiodXLS($periodId);
            if (file_exists($path.$xlsFile)) {
                $this->_prepareDownloadResponse($xlsFile, array('type' => 'filename', 'value' => $path.$xlsFile, 'rm' => true));
            }
            //unlink($path.$file);
        }

        //$this->_redirect('hordermanager/adminhtml_period/view', array('period_id'=> $periodId));
    }
}