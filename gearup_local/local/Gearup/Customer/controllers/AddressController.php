<?php
require_once(Mage::getModuleDir('controllers','Mage_Customer').DS.'AddressController.php');
class Gearup_Customer_AddressController extends Mage_Customer_AddressController
{
    public function formPostAction()
    { 
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
        }
        // Save data
        if ($this->getRequest()->isPost()) {
            $customer = $this->_getSession()->getCustomer();
            /* @var $address Mage_Customer_Model_Address */
            $address  = Mage::getModel('customer/address');
            $addressId = $this->getRequest()->getParam('id');
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
            $addressData    = $addressForm->extractData($this->getRequest());
            $addressErrors  = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                $errors = $addressErrors;
            }
    
            $phone = preg_replace('/[\s-]+/', '', $addressData['telephone']);
            $addressData['telephone'] = $phone;
            
            try {
                $addressForm->compactData($addressData);
                $address->setCustomerId($customer->getId())
                    ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                    ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));

                $addressErrors = $address->validate();
                if ($addressErrors !== true) {
                    $errors = array_merge($errors, $addressErrors);
                }

                if (count($errors) === 0) {
                    $address->save();
                    $this->_getSession()->addSuccess($this->__('The address has been saved.'));
                    $this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure'=>true)));
                    return;
                } else {
                    $this->_getSession()->setAddressFormData($this->getRequest()->getPost());
                    foreach ($errors as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->setAddressFormData($this->getRequest()->getPost())
                    ->addException($e, $e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->setAddressFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save address.'));
            }
        }

        return $this->_redirectError(Mage::getUrl('*/*/edit', array('id' => $address->getId())));
    }

    public function defualtAddressAction()
    {

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        /* @var $address Mage_Customer_Model_Address */
        $address  = Mage::getModel('customer/address');
        //$addressId = $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getPost();

        $addressId = $params['id'];
        $defaultBilling = $params['set_billing'];
        $defaultShipping = $params['set_shipping'];

        if ($addressId) {
            $existsAddress = $customer->getAddressById($addressId);
            if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                $address = $existsAddress;
            }
        }

        if(!$address->getId()) {
            return;
        }

               
        $address->setCustomerId($customer->getId())
                ->setIsDefaultBilling($defaultBilling)
                ->setIsDefaultShipping($defaultShipping);

        try{
            $address->save();
        }catch (Exception $e) {
           $this->_getSession()->addError($this->__('Cannot save address.'));
        }
        if($defaultBilling){
            $this->_getSession()->addSuccess($this->__('Your billing address has been updated.'));
        }else{
            $this->_getSession()->addSuccess($this->__('Your shipping address has been updated.'));
        }
    }
}