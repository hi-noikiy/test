<?php

class Ktpl_Customreport_Block_Adminhtml_Order_View_Tab_Salesinvoicevat extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{    
    //change _constuct to _construct()
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('customreport/order/view/tab/salesinvoicevat.phtml');
    }

    public function getTabLabel() {
        return $this->__('Sales Invoice VAT');
    }

    public function getTabTitle() {
        return $this->__('Sales Invoice VAT');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

    public function getOrder(){
        return Mage::registry('current_order');
    }
} 
?>