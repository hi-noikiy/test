<?php
class EM_Onestepcheckout_Block_Adminhtml_Order_View_Tab_Deliverynote extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{    
    //change _constuct to _construct()
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('onestepcheckout/order/view/tab/deliverynote.phtml');
    }

    public function getTabLabel() {
        return $this->__('Delivery Note');
    }

    public function getTabTitle() {
        return $this->__('Delivery Note');
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