<?php

class Gearup_Payfort_Block_Form_Installments extends Mage_Payment_Block_Form {

    /**
     * Set template and redirect message
     */
    protected function _construct() {
        $this->setTemplate('payfort/pay/installments.phtml')->setMethodLabelAfterHtml('');
        return parent::_construct();
    }

    public function getMethodCode() {
        return PAYFORT_FORT_PAYMENT_METHOD_INSTALLMENTS;
    }

    public function getBankss(){
        $bankss = Mage::getResourceModel('gearup_emi/banks_collection')
                ->addStoreFilter(Mage::app()->getStore())
                ->addFieldToFilter('status', 1)
                ->setOrder('title', 'asc');

        return $bankss;
    }

}
