<?php

class Ktpl_Customreport_Block_Adminhtml_Order_View_Tab_Cimorderemail extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{    
    //change _constuct to _construct()
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('customreport/order/view/tab/cimorderemail.phtml');
    }

    public function getTabLabel() {
        return $this->__('Send Emails');
    }

    public function getTabTitle() {
        return $this->__('Send Emails');
    }

    public function canShowTab() {
        if($this->getOrder()->getIscimorder()) {
        return true;
        } else { return false; }
    }

    public function isHidden() {
        return false;
    }

    public function getOrder(){
        return Mage::registry('current_order');
    }
} 
?>