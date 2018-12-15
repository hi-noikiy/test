<?php
class Ktpl_Repaircenter_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View {
    public function  __construct() {

        parent::__construct();
        $order = $this->getOrder();
        $repairmodel = Mage::getModel('repaircenter/repaircenter')->load($this->getOrder()->getIncrementId(),'increment_id');
        $message = "Are You sure you want to create Repair Request.";
        //if(!$repairmodel->getData()){
        if('1'=='2'){
            $this->_addButton('button_id', array(
                'label'     => Mage::helper('repaircenter')->__('Repair'),
                'onclick'   => "confirmSetLocation('{$message}', '{$this->getUrl('repaircenter/adminhtml_repaircenter/create')}')",
                'class'     => 'go'
            ), 0, 100, 'header', 'header');
       }      
    }
}