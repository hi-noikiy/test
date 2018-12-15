<?php
class EM_Apiios_Model_Api2_Checkout_Onepage_Output_Billing extends EM_Apiios_Model_Api2_Checkout_Onepage_Output_Abstract
{
    /**
     * Sales Qoute Billing Address instance
     *
     * @var Mage_Sales_Model_Quote_Address
     */
    protected $_address;

    /**
     * Customer Taxvat Widget block
     *
     * @var Mage_Customer_Block_Widget_Taxvat
     */
    protected $_taxvat;
    
    public function  __construct() {
       $this->getCheckout()->setStepData('billing', array(
            'label'     => Mage::helper('checkout')->__('Billing Information'),
            'is_show'   => $this->isShow()
        ));
        if ($this->isCustomerLoggedIn()) {
            $this->getCheckout()->setStepData('billing', 'allow', true);
        }
        parent::__construct();
    }

    public function isUseBillingAddressForShipping()
    {
        if (($this->getQuote()->getIsVirtual())
            || !$this->getQuote()->getShippingAddress()->getSameAsBilling()) {
            return false;
        }
        return true;
    }

    /**
     * Return country collection
     *
     * @return Mage_Directory_Model_Mysql4_Country_Collection
     */
    public function getCountries()
    {
        return Mage::getResourceModel('directory/country_collection')->loadByStore();
    }

    /**
     * Return checkout method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getQuote()->getCheckoutMethod();
    }

    /**
     * Return Sales Quote Address model
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
        if (is_null($this->_address)) {
            if ($this->isCustomerLoggedIn()) {
                $this->_address = $this->getQuote()->getBillingAddress();
                if(!$this->_address->getFirstname()) {
                    $this->_address->setFirstname($this->getQuote()->getCustomer()->getFirstname());
                }
                if(!$this->_address->getLastname()) {
                    $this->_address->setLastname($this->getQuote()->getCustomer()->getLastname());
                }
            } else {
                $this->_address = Mage::getModel('sales/quote_address');
            }
        }

        return $this->_address;
    }

    /**
     * Return Customer Address First Name
     * If Sales Quote Address First Name is not defined - return Customer First Name
     *
     * @return string
     */
    public function getFirstname()
    {
        $firstname = $this->getAddress()->getFirstname();
        if (empty($firstname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getFirstname();
        }
        return $firstname;
    }

    /**
     * Return Customer Address Last Name
     * If Sales Quote Address Last Name is not defined - return Customer Last Name
     *
     * @return string
     */
    public function getLastname()
    {
        $lastname = $this->getAddress()->getLastname();
        if (empty($lastname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getLastname();
        }
        return $lastname;
    }

    /**
     * Check is Quote items can ship to
     *
     * @return boolean
     */
    public function canShip()
    {
        return !$this->getQuote()->isVirtual();
    }

    public function getSaveUrl()
    {
    }

    /**
     * Get Customer Taxvat Widget block
     *
     * @return Mage_Customer_Block_Widget_Taxvat
     */
    protected function _getTaxvat()
    {
        if (!$this->_taxvat) {
            $this->_taxvat = $this->getLayout()->createBlock('customer/widget_taxvat');
        }

        return $this->_taxvat;
    }

    /**
     * Check whether taxvat is enabled
     *
     * @return bool
     */
    public function isTaxvatEnabled()
    {
        return $this->_getTaxvat()->isEnabled();
    }

    public function toArrayFields(){
        /* Login Information */
        $fields = array();
        $helper = Mage::helper('checkout');

        /* Select address field */
        if($this->customerHasAddresses()){
            $fields[] = $this->getAddressesSelect('billing');
        }

        /* Address id field */
        $field = array();
        $field['label'] = '';
        $field['required'] = false;
        $field['name'] = 'address_id';
        $field['type'] = 'hidden';
        //$field['value']= $this->getAddress()->getId();
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
        if(!$this->isCustomerLoggedIn()){
            $field = array();
            $field['label'] = $helper->__('Email Address');
            $field['required'] = true;
            $field['name'] = 'email';
            $field['type'] = 'text';
            $fields[] = $field;
        }

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

        $helperAddress = Mage::helper('apiios/address')->setStore($this->getStore());
        /* Region Field */
        $field = array();
        $field['label'] = $helper->__('State/Province');
        $field['required'] = true;
        $field['name'] = 'region_id';
        $field['type'] = 'select';
        $field['options'] = $helperAddress->getRegionArray();
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
        $field['options'] = Mage::helper('apiios/address')->setStore($this->getStore())->getCountryArray();
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

        if(!$this->isCustomerLoggedIn()){
            /* Dob field */
            if($dob = Mage::getModel('apiios/api2_customer_widget_dob')->setStore($this->getStore())->buildFieldList()){
                $fields[] = $dob;
            }

            /* Gender field */
            if($gender = Mage::getModel('apiios/api2_customer_widget_gender')->setStore($this->getStore())->buildFieldList()){
                $fields = array_merge($fields, $gender);
            }

            /* Tax/Vat field */
            if($tax = Mage::getModel('apiios/api2_customer_widget_taxvat')->setStore($this->getStore())->setData('tax_vat',$this->getQuote()->getCustomerTaxvat())->buildFieldList()){
                $fields = array_merge($fields, $tax);
            }

            if(Mage::getSingleton('checkout/type_onepage')->getCheckoutMethod() == 'register'){
                $formCaptchaId = 'guest_checkout';
                /* Customer password field */
                $field = array();
                $field['label'] = $helper->__('Password');
                $field['required'] = true;
                $field['name'] = 'customer_password';
                $field['type'] = 'password';
                $fields[] = $field;

                /* Confirm password field */
                $field = array();
                $field['label'] = $helper->__('Confirm Password');
                $field['required'] = true;
                $field['name'] = 'confirm_password';
                $field['type'] = 'password';
                $fields[] = $field;
            } else {
                $formCaptchaId = 'register_during_checkout';
            }
           
            /* Captcha Field */
            $captcha = Mage::getModel('apiios/captcha_zend',array('formId' => $formCaptchaId))->setStore($this->getStore());
            if($captcha->isRequired()){
                $captcha->generate();
                $field = array();
                $field['label'] = Mage::helper('captcha')->__('Please type the letters below');
                $field['required'] = true;
                $field['name'] = 'captcha_'.$formCaptchaId;
                $field['type'] = 'captcha';
                $field['img_src'] = $captcha->getImgSrc();
                $fields[] = $field;
            }
        }
        
        if ($this->canShip()){
            /* Can ship field */
            $field = array();
            $field['label'] = Mage::helper('apiios')->__('Ship to this address');
            $field['required'] = true;
            $field['name'] = 'use_for_shipping';
            $field['type'] = 'checkbox';
            $field['options'] = array(
                array(
                    'value' =>  1,
                    'label' =>  $helper->__('Yes')
                ),
                array(
                    'value' =>  0,
                    'label' =>  $helper->__('No')
                )
            );
            $fields[] = $field;
        } else {
            $field = array();
            $field['label'] = '';
            $field['required'] = true;
            $field['name'] = 'use_for_shipping';
            $field['type'] = 'hidden';
            $field['value'] = 1;
            $fields[] = $field;
        }

        $result =  array(
            'update_section'  =>  array(
                'name' =>   'billing',
                'json_form'=> array(
                    'title' =>  $helper->__('Billing Information'),
                    'prefix'=>  'billing',
                    'fields'=>  $fields
                )
            )
        );
        if($this->isCustomerLoggedIn()){
            $data = $this->getAddress()->getData();
            $data['firstname'] = $this->getFirstname();
            $data['lastname'] = $this->getLastname();
            $data['address_id'] = $this->getAddress()->getId();

            /* Get available address id */
            $addressId = $this->getAddress()->getCustomerAddressId();
            if (empty($addressId)) {
                $address = $this->getCustomer()->getPrimaryBillingAddress();
                if ($address) {
                    $addressId = $address->getId();
                }
            }
            $data['private_address_id'] = $addressId;
            $result['address_data'] = $data;
        }
        
        return $result;
    }
}
?>
