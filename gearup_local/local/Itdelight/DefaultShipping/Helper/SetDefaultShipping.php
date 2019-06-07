<?php

/**
 * Class Itdelight_DefaultShipping_Helper_SetDefaultShipping
 */
class Itdelight_DefaultShipping_Helper_SetDefaultShipping extends Mage_Core_Helper_Abstract
{
    /**
     * @var Mage_Checkout_Model_Type_Onepage|Oye_Checkout_Model_Type_Onepage
     */
    protected $onepage;
    /**
     * @var Mage_Checkout_Model_Session
     */
    protected $checkoutSession;

    /**
     * @var Mage_Checkout_Block_Onepage_Shipping_Method_Available
     */
    protected $availableMethods;

    /**
     * Itdelight_DefaultShipping_Helper_SetDefaultShipping constructor.
     */
    public function __construct()
    {
        $this->onepage = Mage::getSingleton('checkout/type_onepage');
        $this->checkoutSession = Mage::getSingleton('checkout/session');
        $this->availableMethods = Mage::app()->getLayout()->createBlock('checkout/onepage_shipping_method_available');
    }

    /**
     * @return string
     */
    protected function getDefaultShippingMethod()
    {
        $rates = $this->availableMethods->getShippingRates();
        $code = '';
        if ($rate = reset($rates)[0]) {
            $code = reset($rates)[0]->getCode();
        }
        return $code;
    }

    /**
     * Sets default shipping method on checkout (selects first by order)
     */
    public function setDefaultShippingMethod()
    {
        $checkoutQuote = $this->checkoutSession->getQuote();
        if (!$checkoutQuote->getShippingAddress()->getShippingMethod()) {
            $checkoutQuote->getShippingAddress()->setShippingMethod($this->getDefaultShippingMethod());
            $checkoutQuote->collectTotals();
            $checkoutQuote->save();
        }

        $quote = $this->onepage->getQuote();
        $shipping_address = $quote->getShippingAddress();
        $shipping_method = $shipping_address->getShippingMethod();
        if (!$shipping_method || $shipping_method == '') {
            $quote->getShippingAddress()->setShippingMethod($this->getDefaultShippingMethod());
            $quote->collectTotals()->save();
        }

    }
}