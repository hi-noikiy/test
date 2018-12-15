<?php

class Ktpl_Customreport_Block_Adminhtml_Pickuporder_Grid_Renderer_Customeremail extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        $order_id = $row->getData('real_order_id');
        $order = Mage::getModel('sales/order')->load($order_id);
        $email = $order->getCustomerEmail();
        
        
        return $email;
    }
}