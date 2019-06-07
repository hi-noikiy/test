<?php


class Gearup_Autoinvoice_Adminhtml_Autoinvoice_HistoryController extends Gearup_Autoinvoice_Controller_Adminhtml_Autoinvoice
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
            $historyCollec = Mage::getModel('gearup_autoinvoice/history')->getCollection();
            if ($historyCollec->getSize()) {
                foreach ($historyCollec as $history) {
                    $history->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->__('History are cleaned')
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