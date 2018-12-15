<?php
class EM_Apiios_Helper_Paypalexpress_Address extends EM_Apiios_Helper_Address
{
    protected $_address = null;
    protected $_prefix = null;
    protected $_store = null;

    public function setAddress($object){
        $this->_address = $object;
        return $this;
    }

    public function getAddress(){
        return $this->_address;
    }

    public function setPrefix($prefix){
        $this->_prefix = $prefix;
        return $this;
    }

    public function getPrefix(){
        return $this->_prefix;
    }

    public function setStore($store){
        $this->_store = $store;
        return $this;
    }

    public function getStore(){
        return $this->_store;
    }

    /**
     * Check user login
     * @return bool
     */
    protected function isCustomerLoggedIn(){
        if(!Mage::registry('customer'))
            return false;
        $customerArray = Mage::registry('customer');
        return $customerArray['type'] != 'guest';
    }

    public function loadFields(){
        $fields = array();
        $helper = Mage::helper('checkout');

        /* Address id field */
        $field = array();
        $field['label'] = '';
        $field['required'] = false;
        $field['name'] = 'address_id';
        $field['type'] = 'hidden';
        $fields[] = $field;

        $widgetName = Mage::getModel('apiios/api2_customer_widget_name')->setStore($this->getStore());
        $fields = array_merge($fields,$widgetName->buildFieldList());

        /* Company Field */
        $field = array();
        $field['label'] = $helper->__('Company');
        $field['required'] = false;
        $field['name'] = 'company';
        $field['type'] = 'text';
        $fields[] = $field;

        /* Email Address Field */
        /*if(!$this->isCustomerLoggedIn()){
            $field = array();
            $field['label'] = $helper->__('Email Address');
            $field['required'] = true;
            $field['name'] = 'email';
            $field['type'] = 'text';
            $fields[] = $field;
        }*/

        /* Street Field */
        $field = array();
        $field['label'] = $helper->__('Street Address');
        $field['required'] = true;
        $field['name'] = 'street';
        $field['type'] = 'text';
        $fields[] = $field;

        for ($_i = 2, $_n = Mage::helper('customer/address')->getStreetLines(); $_i <= $_n; $_i++){
            $field = array();
            $field['label'] = $helper->__('Street Address');
            $field['required'] = false;
            $field['name'] = 'street';
            $field['type'] = 'text';
            $fields[] = $field;
        }

        if (Mage::helper('customer/address')->isVatAttributeVisible()){
            $field = array();
            $field['label'] = $helper->__('VAT Number');
            $field['required'] = false;
            $field['name'] = 'vat_id';
            $field['type'] = 'text';
            $fields[] = $field;
        }

        $field = array();
        $field['label'] = $helper->__('City');
        $field['required'] = true;
        $field['name'] = 'city';
        $field['type'] = 'text';
        $fields[] = $field;

        //$helperAddress = Mage::helper('apiios/address')->setStore($this->getStore());
        /* Region Field */
        $field = array();
        $field['label'] = $helper->__('State/Province');
        $field['required'] = true;
        $field['name'] = 'region_id';
        $field['type'] = 'select';
        $field['options'] = $this->getRegionArray();
        $fields[] = $field;

        /* Postcode Field */
        $field = array();
        $field['label'] = $helper->__('Zip/Postal Code');
        $field['required'] = true;
        $field['name'] = 'postcode';
        $field['type'] = 'text';
        $fields[] = $field;

        /* Country Field */
        $field = array();
        $field['label'] = $helper->__('Country');
        $field['required'] = true;
        $field['name'] = 'country_id';
        $field['type'] = 'select';
        $field['options'] = $this->getCountryArray();
        $field['default'] = Mage::helper('core')->getDefaultCountry();
        $fields[] = $field;

        /* Telephone field */
        $field = array();
        $field['label'] = $helper->__('Telephone');
        $field['required'] = true;
        $field['name'] = 'telephone';
        $field['type'] = 'text';
        $fields[] = $field;

        /* Fax field */
        $field = array();
        $field['label'] = $helper->__('Fax');
        $field['required'] = false;
        $field['name'] = 'fax';
        $field['type'] = 'text';
        $fields[] = $field;

        if($this->getPrefix() == 'billing'){
            $field = array();
            $field['label'] = $helper->__('Same as shipping');
            $field['required'] = false;
            $field['name'] = 'as_shipping';
            $field['type'] = 'checkbox';
            $fields[] = $field;
        }
        return $fields;
    }
}