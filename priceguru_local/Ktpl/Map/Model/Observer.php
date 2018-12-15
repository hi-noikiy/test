<?php
class Ktpl_Map_Model_Observer {
    public function setlatitude($observer) {
        $order = $observer->getEvent()->getOrder();
        
        if($order->getCustomerId()){
            $shippingId = $order->getShippingAddress()->getId(); 
            $address    = Mage::getModel('sales/order_address')->load($shippingId);
            $cadd = $address->getCustomerAddressId();
            $caddress = Mage::getModel('customer/address')->load($cadd);
            $address->setLatitude($caddress->getLatitude());
            $address->setLongitude($caddress->getLongitude());
            $address->save();
            
        }
    }
}
?>