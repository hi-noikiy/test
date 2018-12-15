<?php
/**
 * Paypal Express API checkout block for Billing Address
 *
 * @category   EM
 * @package    EM_Apiios
 * @author     emthemes <emthemes.com>
 */
class EM_Apiios_Model_Api2_Paypalexpress_Output_Billing extends EM_Apiios_Model_Api2_Checkout_Onepage_Output_Billing
{
    protected $_quote = null;
    /**
     * Return Sales Quote Address model
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
        if (is_null($this->_address)) {
            if ($this->isCustomerLoggedIn() || $this->_getQuote()->getBillingAddress()) {
                $this->_address = $this->_getQuote()->getBillingAddress();
                if (!$this->_address->getFirstname()) {
                    $this->_address->setFirstname($this->_getQuote()->getCustomer()->getFirstname());
                }
                if (!$this->_address->getLastname()) {
                    $this->_address->setLastname($this->_getQuote()->getCustomer()->getLastname());
                }
            } else {
                $this->_address = Mage::getModel('sales/quote_address');
            }
        }

        return $this->_address;
    }

    public function loadFields(){
        $helper = Mage::helper('apiios/paypalexpress_address')->setStore($this->getStore())->setPrefix('billing');

        $fields = $helper->loadFields();

        $data = $this->getAddress()->getData();
        $data['firstname'] = $this->getFirstname();
        $data['lastname'] = $this->getLastname();
        $data['address_id'] = $this->getAddress()->getId();
        $data['as_shipping'] = $this->getAddress()->getSameAsBilling();

        /* Get available address id */
        /*$addressId = $this->getAddress()->getCustomerAddressId();
        if (empty($addressId)) {
            $address = $this->getCustomer()->getPrimaryBillingAddress();
            if ($address) {
                $addressId = $address->getId();
            }
        }*/
        //$data['private_address_id'] = $addressId;
        //$result['address_data'] = $data;

        $result =  array(
            'update_section'  =>  array(
                'name' =>   'billing',
                'json_form'=> array(
                    'title' =>  Mage::helper('checkout')->__('Billing Address'),
                    'prefix'=>  'billing',
                    'fields'=>  $fields
                ),
                'data'  =>  $data
            )
        );
        return $result;
    }

    /**
     * Return checkout quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    private function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Return checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
//    	return Mage::getSingleton('checkout/session');
        return Mage::getSingleton('apiios/api2_checkout_session')->setStore($this->getStore());
    }
}