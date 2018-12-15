<?php
class EM_Apiios_Model_Api2_Customer_Address_Rest_Guest_V1 extends Mage_Customer_Model_Api2_Customer_Address
{
    protected $_customer = null;

    /**
     * Update information address
     */
    protected function  _create($addressData) {
        /* Translate text */
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);

        // Save data
        $customer = $this->getCustomer();
        /* @var $address Mage_Customer_Model_Address */
        $address  = Mage::getModel('customer/address');
        $addressId = isset($addressData['id']) ? $addressData['id'] : null;
        if ($addressId) {
            $existsAddress = $customer->getAddressById($addressId);
            if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                $address->setId($existsAddress->getId());
            }
        }

        $errors = array();

        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
            ->setEntity($address);
        $addressErrors  = $addressForm->validateData($addressData);
        if ($addressErrors !== true) {
            $errors = $addressErrors;
        }

        try {
            $addressForm->compactData($addressData);
            $address->setCustomerId($customer->getId())
                ->setIsDefaultBilling((isset($addressData['default_billing'])) ? true : false)
                ->setIsDefaultShipping((isset($addressData['default_shipping'])) ? true : false);

            $addressErrors = $address->validate();
            if ($addressErrors !== true) {
                $errors = array_merge($errors, $addressErrors);
            }

            if (count($errors) === 0) {
                $address->save();
                $this->_successMessage(
                   Mage::helper('customer')->__('The address has been saved.'),
                    Mage_Api2_Model_Server::HTTP_OK,
                    array('id'=>$address->getId())
                );
            } else {
                foreach ($errors as $errorMessage) {
                    $this->_errorMessage($errorMessage, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                }
            }
        } catch (Mage_Core_Exception $e) {
            $this->_errorMessage($e->getMessage(), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            $this->_errorMessage(Mage::helper('customer')->__('Cannot save address.'), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        $this->_render($this->getResponse()->getMessages());
        $this->getResponse()->setHttpResponseCode(Mage_Api2_Model_Server::HTTP_MULTI_STATUS);
    }


    protected function  _retrieveCollection() {
        /* Translate text */
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);

        $customer = Mage::getModel('customer/customer')->load(2);
        $result = array();$billing = array();$shipping = array();$additional = array();
        $helper = Mage::helper('customer');
        
        /* Get Billing Address */
        if($_pAddsses = $customer->getDefaultBilling()){
            $billing['data'] = $customer->getAddressById($_pAddsses)->getData();
        } else{
            $billing['message'] = $helper->__('You have no default billing address in your address book.');
        }
        $billing['title'] = $helper->__('Default Billing Address');
        $result['billing'] = $billing;

        /* Get Shipping Address */
        if($_pAddsses = $customer->getDefaultShipping()){
            $shipping['data'] = $customer->getAddressById($_pAddsses)->getData();
        } else {
            $shipping['message'] = $helper->__('You have no default shipping address in your address book.');
        }
        $shipping['title'] = $helper->__('Default Shipping Address');
        $result['shipping'] = $shipping;

        /* Get Additional Address */
        $additionalAddress = $customer->getAdditionalAddresses();
        if(!empty($additionalAddress)){
            $data = array();
            foreach($additionalAddress as $address){
                $data[] = $address->getData();
            }
            $additional['data'] = $data;
        }
        $additional['title'] = $helper->__('Additional Address Entries');
        $result['additional'] = $additional;
        return array('address'=>$result);
    }

    /**
     * Get Current Customer
     * @return Mage_Customer_Model_Customer
     */
    protected function getCustomer(){
        if(!$this->_customer)
            $this->_customer = Mage::getModel('customer/customer')->load(2);
        return $this->_customer;
    }

   
}
?>