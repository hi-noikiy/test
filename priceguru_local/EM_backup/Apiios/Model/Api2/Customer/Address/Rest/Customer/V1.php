<?php
class EM_Apiios_Model_Api2_Customer_Address_Rest_Customer_V1 extends Mage_Customer_Model_Api2_Customer_Address
{
    protected $_customer = null;
    protected $_address = null;

    /**
     * Save information address
     */
    protected function  _saveAddress($addressData) {
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
    }

    /**
     * Update information address (for ios)
     */
    protected function _create($data){
        $this->_saveAddress($data);
        $this->_render($this->getResponse()->getMessages());
    }

    /**
     * Update information address (for android)
     */
    protected function _multiCreate($data){
        $this->_saveAddress($data[0]);
    }

    /**
     * Get address list include default shipping, default billing and additional address
     * @return array
     */
    protected function  _retrieveCollection() {
        /* Translate text */
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);

        $customer = $this->getCustomer();
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
     * Get information for an address and fields in edit address form
     * @return array
     */
    protected function  _retrieve() {
        /* Translate text */
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        
        $fields = $this->_loadFields();
        if($id = $this->getRequest()->getParam('id')){
            $fields['address_data'] = $this->getAddress()->getData();
            $fields['address_data']['region_id'] = $this->getRegionId();
            $street = array();
            for ($_i = 1, $_n = Mage::helper('customer/address')->getStreetLines(); $_i <= $_n; $_i++){
                $street[] = $this->getAddress()->getStreet($_i);
            }
            $fields['address_data']['street'] = $street;
        }
        $fields['formkey'] = Mage::helper('core')->getRandomString(16);
        return array('field_list' => $fields);
    }

     /**
     * Load fields for edit form
     * @return array
     */
    protected function  _loadFields(){
        $helper = Mage::helper('customer');
        $result = array();
        
        /* Get Contact Information */
        /* Get field from customer/widget_name block */
        $widgetName = Mage::getModel('apiios/api2_customer_widget_name')->setStore($this->_getStore());
        $contact = $widgetName->buildFieldList();
        
        /* Company Field */
        $field = array();
        $field['label'] = $helper->__('Company');
        $field['required'] = false;
        $field['name'] = 'company';
        $field['type'] = 'text';
        $contact[] = $field;

        /* Telephone Field */
        $field = array();
        $field['label'] = $helper->__('Telephone');
        $field['required'] = true;
        $field['name'] = 'telephone';
        $field['type'] = 'text';
        $contact[] = $field;

        /* Fax Field */
        $field = array();
        $field['label'] = $helper->__('Fax');
        $field['required'] = false;
        $field['name'] = 'fax';
        $field['type'] = 'text';
        $contact[] = $field;

        /* Get Address Information */
        $address = array();
        
        /* Street Field */
        $field = array();
        $field['label'] = $helper->__('Street Address');
        $field['required'] = true;
        $field['name'] = 'street';
        $field['type'] = 'text';
        $address[] = $field;

        for ($_i = 2, $_n = Mage::helper('customer/address')->getStreetLines(); $_i <= $_n; $_i++){
            $field = array();
            $field['label'] = $helper->__('Street Address');
            $field['required'] = false;
            $field['name'] = 'street';
            $field['type'] = 'text';
            $address[] = $field;
            //$street[] = $this->getAddress()->getStreet($_i);
        }

        if(Mage::helper('customer/address')->isVatAttributeVisible()){
            /* VAT Number Field */
            $field = array();
            $field['label'] = $helper->__('VAT Number');
            $field['required'] = false;
            $field['name'] = 'vat_id';
            $field['type'] = 'text';
            $address[] = $field;
        }

        /* City Field */
        $field = array();
        $field['label'] = $helper->__('City');
        $field['required'] = true;
        $field['name'] = 'city';
        $field['type'] = 'text';
        $address[] = $field;

        $helperAddress = Mage::helper('apiios/address')->setStore($this->_getStore());
        /* Region Field */
        $field = array();
        $field['label'] = $helper->__('State/Province');
        $field['required'] = false;
        $field['name'] = 'region_id';
        $field['type'] = 'select';
        $field['options'] = $helperAddress->getRegionArray();
        $address[] = $field;

        /* Postcode Field */
        $field = array();
        $field['label'] = $helper->__('Zip/Postal Code');
        $field['required'] = true;
        $field['name'] = 'postcode';
        $field['type'] = 'text';
        $address[] = $field;

        /* Country Field */
        $field = array();
        $field['label'] = $helper->__('Country');
        $field['required'] = true;
        $field['name'] = 'country_id';
        $field['type'] = 'select';
        $field['options'] = Mage::helper('apiios/address')->setStore($this->_getStore())->getCountryArray();
        $field['default'] = Mage::helper('core')->getDefaultCountry();
        $address[] = $field;

        /* Defaull Billing Checkbox Field */
        if($this->canSetAsDefaultBilling()){
            $field = array();
            if($this->isDefaultBilling()){
                $field['label'] = $helper->__('Default Billing Address');
                $field['type'] = 'label';
            } elseif($this->canSetAsDefaultBilling()){
                $field['type'] = 'checkbox';
                $field['label'] = $helper->__('Use as my default billing address');
                $field['required'] = false;
                $field['name'] = 'default_billing';
            }
            $address[] = $field;
        }

        /* Defaull Shipping Checkbox Field */
        if($this->canSetAsDefaultShipping()){
            $field = array();
            if($this->isDefaultShipping()){
                $field['label'] = $helper->__('Default Shipping Address');
                $field['type'] = 'label';
            } elseif($this->canSetAsDefaultShipping()){
                $field['type'] = 'checkbox';
                $field['label'] = $helper->__('Use as my default shipping address');
                $field['required'] = false;
                $field['name'] = 'default_shipping';
            }
            $address[] = $field;
        }

        $result['contact'] = array(
            'title'     =>  $helper->__('Contact Information'),
            'fields'    =>  $contact
        );

        $result['address'] = array(
            'title'     =>  $helper->__('Address'),
            'fields'    =>  $address
        );

        return $result;
    }

    /**
     * Get Current Customer
     * @return Mage_Customer_Model_Customer
     */
    protected function getCustomer(){
        if(!$this->_customer)
            $this->_customer = Mage::getModel('customer/customer')->load($this->getApiUser()->getUserId());
        return $this->_customer;
    }

    protected function getAddress(){
        $this->_address = Mage::getModel('customer/address');
        // Init address object
        if ($id = $this->getRequest()->getParam('id')) {
            $this->_address->load($id);
            if ($this->_address->getCustomerId() != $this->getApiUser()->getUserId()) {
                $this->_address->setData(array());
            }
        }
        return $this->_address;
    }

    public function getRegionId()
    {
        return $this->getAddress()->getRegionId();
    }

    public function getCustomerAddressCount()
    {
        return count($this->getCustomer()->getAddresses());
    }

    public function canSetAsDefaultBilling()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getCustomerAddressCount();
        }
        return !$this->isDefaultBilling();
    }

    public function canSetAsDefaultShipping()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getCustomerAddressCount();
        }
        return !$this->isDefaultShipping();;
    }

    public function isDefaultBilling()
    {
        $defaultBilling = $this->getCustomer()->getDefaultBilling();
        return $this->getAddress()->getId() && $this->getAddress()->getId() == $defaultBilling;
    }

    public function isDefaultShipping()
    {
        $defaultShipping = $this->getCustomer()->getDefaultShipping();
        return $this->getAddress()->getId() && $this->getAddress()->getId() == $defaultShipping;
    }
}
?>