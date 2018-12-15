<?php
class EM_Apiios_Model_Api2_Checkout_Onepage_Output_Shipping extends EM_Apiios_Model_Api2_Checkout_Onepage_Output_Abstract
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
       $this->getCheckout()->setStepData('shipping', array(
            'label'     => Mage::helper('checkout')->__('Shipping Information'),
            'is_show'   => $this->isShow()
        ));
        parent::__construct();
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
     * Return Sales Quote Address model (shipping address)
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
        if (is_null($this->_address)) {
            if ($this->isCustomerLoggedIn()) {
                $this->_address = $this->getQuote()->getShippingAddress();
            } else {
                $this->_address = Mage::getModel('sales/quote_address');
            }
        }

        return $this->_address;
    }

    public function toArrayFields(){
        /* Login Information */
        $fields = array();
        $helper = Mage::helper('checkout');

        /* Select address field */
        if($this->customerHasAddresses()){
            $fields[] = $this->getAddressesSelect('shipping');
        }

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
        $field['required'] = true;
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
            if($tax = Mage::getModel('apiios/api2_customer_widget_taxvat')->setStore($this->getStore())->buildFieldList()){
                $fields = array_merge($fields, $tax);
            }

        }
        
        if ($this->isCustomerLoggedIn() && $this->customerHasAddresses()){
            /* Can ship field */
            $field = array();
            $field['label'] = $helper->__('Save in address book');
            $field['required'] = false;
            $field['name'] = 'save_in_address_book';
            $field['type'] = 'checkbox';
            $fields[] = $field;
        } else {
            $field = array();
            $field['label'] = '';
            $field['required'] = false;
            $field['name'] = 'save_in_address_book';
            $field['type'] = 'hidden';
            $field['value'] = 1;
            $fields[] = $field;
        }

        $field = array();
        $field['label'] = $helper->__('Use Billing Address');
        $field['required'] = false;
        $field['name'] = 'same_as_billing';
        $field['type'] = 'checkbox';
        $fields[] = $field;

        $result = array(
                'title' =>  $helper->__('Shipping Information'),
                'prefix'=>  'shipping',
                'fields'=>  $fields
            );
        if($this->isCustomerLoggedIn()){
            $data = $this->getAddress()->getData();
            $data['firstname'] = $this->getFirstname();
            $data['lastname'] = $this->getLastname();
            $data['address_id'] = $this->getAddress()->getId();

            /* Get available address id */
            $addressId = $this->getAddress()->getCustomerAddressId();
            if (empty($addressId)) {
                $address = $this->getCustomer()->getPrimaryShippingAddress();
                if ($address) {
                    $addressId = $address->getId();
                }
            }
            $data['private_address_id'] = $addressId;
            $result['data'] = $data;
        }
        
        return $result;
    }
}
?>
