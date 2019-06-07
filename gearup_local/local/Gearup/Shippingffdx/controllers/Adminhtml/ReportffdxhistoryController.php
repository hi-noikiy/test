<?php

class Gearup_Shippingffdx_Adminhtml_ReportffdxhistoryController extends Gearup_Shippingffdx_Controller_Adminhtml_Shippingffdx
{

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

    public function deleteallAction()
    {
        try {
            $historyCollec = Mage::getModel('gearup_shippingffdx/history')->getCollection();
            if ($historyCollec->getSize()) {
                foreach ($historyCollec as $history) {
                    $history->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Histories are cleaned')
                );
            }
        } catch (Exception $e) {
            $this->_getSession()->addError(
                $e->getMessage()
            );
        }



        $this->_redirect('*/*/index');
    }
}