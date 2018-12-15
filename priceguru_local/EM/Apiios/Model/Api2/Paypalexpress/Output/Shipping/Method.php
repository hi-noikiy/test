<?php
/**
 * Paypal Express API checkout block for Shipping Address
 *
 * @category   EM
 * @package    EM_Apiios
 * @author     emthemes <emthemes.com>
 */
class EM_Apiios_Model_Api2_Paypalexpress_Output_Shipping_Method extends EM_Apiios_Model_Api2_Paypalexpress_Output_Shipping
{
    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote;

    /**
     * Currently selected shipping rate
     *
     * @var Mage_Sales_Model_Quote_Address_Rate
     */
    protected $_currentShippingRate = null;

    /**
     * Paypal action prefix
     *
     * @var string
     */
    protected $_paypalActionPrefix = 'paypal';

    /**
     * Quote object setter
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Paypal_Block_Express_Review
     */
    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->_quote = $quote;
        return $this;
    }

    /**
     * Return quote billing address
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getBillingAddress()
    {
        return $this->_quote->getBillingAddress();
    }

    /**
     * Return quote shipping address
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getShippingAddress()
    {
        if ($this->_quote->getIsVirtual()) {
            return false;
        }
        return $this->_quote->getShippingAddress();
    }

    /**
     * Get HTML output for specified address
     *
     * @param Mage_Sales_Model_Quote_Address
     * @return string
     */
    public function renderAddress($address)
    {
        return $address->getFormated(true);
    }

    /**
     * Return carrier name from config, base on carrier code
     *
     * @param $carrierCode string
     * @return string
     */
    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig("carriers/{$carrierCode}/title")) {
            return $name;
        }
        return $carrierCode;
    }

    /**
     * Get either shipping rate code or empty value on error
     *
     * @param Varien_Object $rate
     * @return string
     */
    public function renderShippingRateValue(Varien_Object $rate)
    {
        if ($rate->getErrorMessage()) {
            return '';
        }
        return $rate->getCode();
    }

    /**
     * Get helper object
     *
     * @param $key
     * @return Mage_Core_Helper_Abstract
     */
    public function helper($key){
        return Mage::helper($key);
    }

    /**
     * Get shipping rate code title and its price or error message
     *
     * @param Varien_Object $rate
     * @param string $format
     * @param string $inclTaxFormat
     * @return string
     */
    public function renderShippingRateOption($rate, $format = '%s - %s%s', $inclTaxFormat = ' (%s %s)')
    {
        $renderedInclTax = '';
        if ($rate->getErrorMessage()) {
            $price = $rate->getErrorMessage();
        } else {
            $price = $this->_getShippingPrice($rate->getPrice(),
                $this->helper('tax')->displayShippingPriceIncludingTax());

            $incl = $this->_getShippingPrice($rate->getPrice(), true);
            if (($incl != $price) && $this->helper('tax')->displayShippingBothPrices()) {
                $renderedInclTax = sprintf($inclTaxFormat, Mage::helper('tax')->__('Incl. Tax'), $incl);
            }
        }
        return sprintf($format, $rate->getMethodTitle(), $price, $renderedInclTax);
    }

    /**
     * Getter for current shipping rate
     *
     * @return Mage_Sales_Model_Quote_Address_Rate
     */
    public function getCurrentShippingRate()
    {
        return $this->_currentShippingRate;
    }

    /**
     * Set paypal actions prefix
     */
    public function setPaypalActionPrefix($prefix)
    {
        $this->_paypalActionPrefix = $prefix;
    }

    /**
     * Return formatted shipping price
     *
     * @param float $price
     * @param bool $isInclTax
     *
     * @return bool
     */
    public function _getShippingPrice($price, $flag)
    {
        return $this->_getQuote()->getStore()->convertPrice(Mage::helper('tax')->getShippingPrice($price, $flag, $this->getAddress()), true, false);
    }

    /**
     * Format price base on store convert price method
     *
     * @param float $price
     * @return string
     */
    protected function _formatPrice($price)
    {
        return $this->_quote->getStore()->convertPrice($price, true);
    }

    /**
     * Retrieve payment method and assign additional template values
     *
     * @return EM_Apiios_Model_Api2_Paypalexpress_Output_Shipping_Method
     */
    protected function _beforeLoadFields()
    {
        $methodInstance = $this->_getQuote()->getPayment()->getMethodInstance();
        $this->setPaymentMethodTitle($methodInstance->getTitle());
        //$this->setUpdateOrderSubmitUrl($this->getUrl("{$this->_paypalActionPrefix}/express/updateOrder"));
        //$this->setUpdateShippingMethodsUrl($this->getUrl("{$this->_paypalActionPrefix}/express/updateShippingMethods"));

        $this->setShippingRateRequired(true);
        if ($this->_getQuote()->getIsVirtual()) {
            $this->setShippingRateRequired(false);
        } else {
            // prepare shipping rates
            $this->_address = $this->_getQuote()->getShippingAddress();
            $groups = $this->_address->getGroupedAllShippingRates();
            if ($groups && $this->_address) {
                $this->setShippingRateGroups($groups);
                // determine current selected code & name
                foreach ($groups as $code => $rates) {
                    foreach ($rates as $rate) {
                        if ($this->_address->getShippingMethod() == $rate->getCode()) {
                            $this->_currentShippingRate = $rate;
                            break(2);
                        }
                    }
                }
            }

            // misc shipping parameters
            //$this->setShippingMethodSubmitUrl($this->getUrl("{$this->_paypalActionPrefix}/express/saveShippingMethod"))
            $this->setCanEditShippingAddress($this->_getQuote()->getMayEditShippingAddress())
                ->setCanEditShippingMethod($this->_getQuote()->getMayEditShippingMethod())
            ;
        }

        //$this->setEditUrl($this->getUrl("{$this->_paypalActionPrefix}/express/edit"))
        //->setPlaceOrderUrl($this->getUrl("{$this->_paypalActionPrefix}/express/placeOrder"));

        return $this;
    }

    public function getAddress()
    {
        if (empty($this->_address)) {
            $this->_address = $this->_getQuote()->getShippingAddress();
        }
        return $this->_address;
    }

    public function  toArrayFields() {
        $this->_beforeLoadFields();
        $result = array();
        if($this->getCanEditShippingMethod() || !$this->getCurrentShippingRate()){
            $_shippingRateGroups = $this->getShippingRateGroups();
            //$_shippingRateGroups = $this->getShippingRates();
            $helper = Mage::helper('checkout');
            if (!$_shippingRateGroups)
                return array('message'=>$helper->__('Sorry, no quotes are available for this order at this time.'));
            $_sole = count($_shippingRateGroups) == 1;
            foreach ($_shippingRateGroups as $code => $_rates){
                $shipping = array();
                $shipping['carrier'] = $this->getCarrierName($code);
                $_sole = $_sole && count($_rates) == 1;
                $rateData = array();
                foreach ($_rates as $_rate){
                    $label = array(
                        'title' =>  $_rate->getMethodTitle()
                    );
                    $_excl = $this->_getShippingPrice($_rate->getPrice(), Mage::helper('tax')->displayShippingPriceIncludingTax());
                    $_incl = $this->_getShippingPrice($_rate->getPrice(), true);
                    $price = $_excl;
                    if (Mage::helper('tax')->displayShippingBothPrices() && $_incl != $_excl){
                        $price .= ' '.$helper->__('Incl. Tax').' '.$_incl;
                    }
                    $label['price'] = $price;
                    $rateData[] = array(
                        'value' =>  $_rate->getCode(),
                        'label' =>  $label
                    );
                }
                $shipping['rates'] = $rateData;
                $result[] = $shipping;
            }
        }


        $final = array(
            'title' =>  $helper->__('Shipping Method'),
            'prefix'=>  'shipping_method',
            'fields'=>  array(
                array(
                    'name'      =>  'shipping_method',
                    'type'      =>  'radio_group',
                    'methods'   =>  $result
                )
            ),
            'ask_update' => Mage::helper('paypal')->__('Please update order data to get shipping methods and rates')
        );
        return $final;
    }
}