<?php

class Ktpl_Customreport_Block_Adminhtml_Pickuporder_Grid_Renderer_Customername extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        $order_id = $row->getData('real_order_id');
        $customer_name = $row->getData('customer_name');

        if($customer_name && trim($customer_name) !='') { 
            $name = $customer_name;   
        } else {
            $order = Mage::getModel('sales/order')->load($order_id);
            //$name = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
            $name = $order->getBillingAddress()->getName();
        }
        
        return $name;
    }
}