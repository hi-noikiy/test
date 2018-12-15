<?php

abstract class Collinsharper_Beanstreaminterac_Model_Abstract extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Get beanstreaminterac API Model
     *
     * @return Collinsharper_Beanstreaminterac_Model_Api_Nvp
     */
    public function getApi()
    {
        return Mage::getSingleton('beanstreaminterac/api_nvp');
    }

    /**
     * Get beanstreaminterac session namespace
     *
     * @return Collinsharper_Beanstreaminterac_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('beanstreaminterac/session');
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    public function getRedirectUrl()
    {
        return $this->getApi()->getRedirectUrl();
    }

    public function getCountryRegionId()
    {
        $a = $this->getApi()->getShippingAddress();
        return $this;
    }
}
