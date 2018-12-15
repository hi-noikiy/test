<?php

class Ktpl_Customreport_Block_Adminhtml_Deliveryorder_Grid_Renderer_City extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        $billingAddress = $row->getData($this->getColumn()->getIndex());
        
        $html = '';
        if(!$billingAddress){
            $order_id = $row->getRealOrderId();
            $order=Mage::getModel('sales/order')->load($order_id);
            if($order->getBillingAddress()){
                $billingAddress = $order->getBillingAddress()->getCity();

            }    
        }
        $html = '<input type="text" style="width:80px" id="city'.$row->getDeliveryId().'" name="'.$this->getColumn()->getId().'" value="'.$billingAddress.'" class="input-text ">';
        return $html;
    }
    
    public function renderExport(Varien_Object $row){
        $billingAddress = $row->getData($this->getColumn()->getIndex());
        
        $html = '';
        if(!$billingAddress){
            $order_id = $row->getRealOrderId();
            $order=Mage::getModel('sales/order')->load($order_id);
            if($order->getBillingAddress()){
               return $order->getBillingAddress()->getCity();
            }  
        }
        return $billingAddress;
    }
}