<?php
/**
 * Observer Model
 */
class Gearup_CartMerge_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * Truncate the customer's cart if active
     * @access public
     * @return void
     */
    public function checkCustomerCart(Varien_Event_Observer $observer)
    {
        if ($observer->getSource()->hasItems()) {
            if (is_object($observer->getQuote()) && $observer->getQuote()->getId()) {
//                $observer->getQuote()->removeAllItems();
            }
        }
    }
}