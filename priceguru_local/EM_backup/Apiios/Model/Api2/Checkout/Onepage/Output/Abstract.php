<?php
class EM_Apiios_Model_Api2_Checkout_Onepage_Output_Abstract extends Mage_Core_Model_Abstract
{
    protected $_checkout;
    protected $_quote;
    protected $_store;

    public function setStore($store){
        $this->_store = $store;
        return $this;
    }

    public function getStore(){
        return $this->_store;
    }

    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * Retrieve checkout session model
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        if (empty($this->_checkout)) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }

    /**
     * Retrieve sales quote model
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (empty($this->_quote)) {
            $this->_quote = $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Get checkout steps codes
     *
     * @return array
     */
    protected function _getStepCodes()
    {
        return array('login', 'billing', 'shipping', 'shipping_method', 'payment', 'review');
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

    public function getCustomer(){
        $customerArray = Mage::registry('customer');
        return $customerArray['customer'];
    }

    public function toArrayFields(){
        return array();
    }

    /**
     * Retrieve is allow and show block
     *
     * @return bool
     */
    public function isShow()
    {
        return true;
    }

    public function customerHasAddresses()
    {
        if($this->getCustomer())
            return count($this->getCustomer()->getAddresses());
        return false;
    }

/* */
    public function getAddressesSelect($type)
    {
        if(!$this->getCustomer())
            return '';
        if ($this->isCustomerLoggedIn()) {
            $field = array();
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }
            $options[] = array(
                'value' =>  '',
                'label' =>  Mage::helper('checkout')->__('New Address')
            );

            $field['label'] = Mage::helper('checkout')->__('Select a billing address from your address book or enter a new address.');
            $field['required'] = true;
            $field['name'] = $type."_address_id";
            $field['type'] = 'drop_down';
            $field['options'] = $options;

            return $field;
        }
        return '';
    }

    protected function _canUseMethod($method)
    {
        if (!$method->canUseForCountry($this->getQuote()->getBillingAddress()->getCountry())) {
            return false;
        }

        if (!$method->canUseForCurrency($this->getQuote()->getStore()->getBaseCurrencyCode())) {
            return false;
        }

        /**
         * Checking for min/max order total for assigned payment method
         */
        $total = $this->getQuote()->getBaseGrandTotal();
        $minTotal = $method->getConfigData('min_order_total');
        $maxTotal = $method->getConfigData('max_order_total');

        if((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
            return false;
        }
        return true;
    }
}
?>