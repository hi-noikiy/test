<?php

class Ktpl_Map_Adminhtml_ShippingorderController extends Mage_Adminhtml_Controller_Action {

    public function SavelatitudeAction()
    {
        //echo '<pre />'; print_r($this->getRequest()->getParams()); 
        $addressId  = $this->getRequest()->getParam('address_id');
        $address    = Mage::getModel('sales/order_address')->load($addressId);
        $billaddress    = Mage::getModel('sales/order_address')->load($this->getRequest()->getParam('bill_id'));
        $cadd = $address->getCustomerAddressId();
        if(!$cadd){$cadd = $billaddress->getCustomerAddressId();}
        $data       = $this->getRequest()->getPost();
        if ($data['latitude'] && $data['longitude'] && $address->getId()) {
            $address->addData($data);
            try {
                if($cadd){
                    $caddress = Mage::getModel('customer/address')->load($cadd);
                    ($caddress->getData()); 
                    $caddress->setLatitude($data['latitude']);
                    $caddress->setLongitude($data['longitude']);
                    $caddress->save();
                } 
                $address->implodeStreetAddress()
                    ->save();
                $this->_getSession()->addSuccess(Mage::helper('sales')->__('The Latitude & Longitude has been saved.'));
                $this->_redirect('adminhtml/sales_order/view', array('order_id'=>$address->getParentId()));
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException(
                    $e,
                    Mage::helper('sales')->__('An error occurred while updating the Latitude & Longitude.')
                );
            }
            $this->_redirect('*/*/', array('address_id'=>$address->getId()));
        } else {
            $this->_redirect('adminhtml/sales_order/view', array('order_id' => $address->getParentId()));
        }
    }
    public function _isAllowed()
    {
        return true;
    }
    
}