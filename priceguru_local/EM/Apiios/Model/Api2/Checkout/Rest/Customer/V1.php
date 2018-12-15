<?php
class EM_Apiios_Model_Api2_Checkout_Rest_Customer_V1 extends EM_Apiios_Model_Api2_Checkout_Rest_Abstract
{
    /**
     * Current logged in customer
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer;

    /**
     * Get customer group
     *
     * @return int
     */
    protected function _getCustomerGroupId()
    {
        return $this->_getCustomer()->getGroupId();
    }

    /**
     * Retrieve current customer
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer()
    {
        if (is_null($this->_customer)) {
            /** @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('customer/customer')->load($this->getApiUser()->getUserId());
            if (!$customer->getId()) {
                $this->_critical('Customer not found.', Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
            $this->_customer = $customer;
        }
        return $this->_customer;
    }

    /**
     * Define product price with or without taxes
     *
     * @param float $price
     * @param bool $withTax
     * @return float
     */
    protected function _applyTaxToPrice($price, $withTax = true)
    {
        $customer = $this->_getCustomer();
        /** @var $session Mage_Customer_Model_Session */
        $session = Mage::getSingleton('customer/session');
        $session->setCustomerId($customer->getId());
        $price = $this->_getPrice($price, $withTax, $customer->getPrimaryShippingAddress(),
            $customer->getPrimaryBillingAddress(), $customer->getTaxClassId());
        $session->setCustomerId(null);

        return $price;
    }
}
?>