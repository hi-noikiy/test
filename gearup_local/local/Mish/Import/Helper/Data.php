<?php

/**
 * Helper
 *
 * @package GearUp.me
 */
class Mish_Import_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Check if checkout is enabled
     * 1 - enabled
     * 0 - disabled
     *
     * @return int
     */
    public function isCheckoutEnabled()
    {
        return (int) Mage::getStoreConfig('checkout/import/enabled');
    }

    /**
     * The Message
     */
    public function getMessage()
    {
        return Mage::getStoreConfig('checkout/import/custom_text');
    }
}