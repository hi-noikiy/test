<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Adminhtml_Renderer_Currency
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Currency
{
    /**
     * @var string[]
     */
    protected static $_websiteCurrencyCodes = array();

    /**
     * @param Varien_Object $row
     *
     * @return string
     */
    protected function _getCurrencyCode($row)
    {
        $websiteId = $row->getData('website_id');
        if ($row->getData('base_currency_code') !== null) {
            return $row->getData('base_currency_code');
        }
        if (!isset(self::$_websiteCurrencyCodes[$websiteId])) {
            self::$_websiteCurrencyCodes[$websiteId] = Mage::app()->getWebsite($websiteId)->getBaseCurrencyCode();
        }
        return self::$_websiteCurrencyCodes[$websiteId];
    }

    /**
     * @param Varien_Object $row
     *
     * @return int
     */
    protected function _getRate($row)
    {
        return 1;
    }
}
