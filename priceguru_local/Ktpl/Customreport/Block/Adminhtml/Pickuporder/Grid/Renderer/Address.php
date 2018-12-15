<?php

class Ktpl_Customreport_Block_Adminhtml_Pickuporder_Grid_Renderer_Address extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        $billingAddress = '';
        
        $order_id = $row->getRealOrderId();
        $order=Mage::getModel('sales/order')->load($order_id);
        if($order->getBillingAddress()){
            $bill = $order->getBillingAddress()->getStreet();
            $billingAddress = $bill[0].$bill[1].','.$order->getBillingAddress()->getPostcode();
        }    
       
        return $billingAddress;
    }
}