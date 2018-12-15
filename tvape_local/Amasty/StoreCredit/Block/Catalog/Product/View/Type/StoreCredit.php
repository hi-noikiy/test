<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Block_Catalog_Product_View_Type_StoreCredit extends Mage_Catalog_Block_Product_View_Abstract
{


    public function displayProductStockStatus()
    {
        if (method_exists('Mage_Catalog_Block_Product_View_Abstract', 'displayProductStockStatus')) {
            return parent::displayProductStockStatus();
        }
        $statusInfo = new Varien_Object(array('display_status' => true));
        Mage::dispatchEvent('catalog_block_product_status_display', array('status' => $statusInfo));
        return (boolean)$statusInfo->getDisplayStatus();
    }

    /**
     * @return bool
     */
    public function isMultiAmount()
    {
        $product = $this->getProduct();
        return $product->getPriceModel()->isMultiAmount($product);
    }

    /**
     * @return bool
     */
    public function isPredefinedAmount()
    {
        return count($this->getListAmounts()) > 0;
    }


    /**
     * @return array
     */
    public function getListAmounts()
    {
        //return array();
        $product = $this->getProduct();
        $listAmounts = array();
        foreach ($product->getPriceModel()->getAmounts($product) as $amount) {
            $listAmounts[] = Mage::app()->getStore()->roundPrice($amount['website_value']);
        }
        return $listAmounts;
    }


    public function getPricePercent()
    {

        $_product = $this->getProduct();
        return $_product->getAmstcredPriceType() == Amasty_StoreCredit_Model_StoreCredit::PRICE_TYPE_PERCENT ?
            $_product->getAmstcredPricePercent() :
            100;
    }


    public function isConfigured()
    {
        $product = $this->getProduct();
        if (!$product->getAmstcredAllowOpenAmount() && !$this->getListAmounts()) {
            return false;
        }
        return true;
    }


    public function getDefaultValue($key)
    {
        return (string)$this->getProduct()->getPreconfiguredValues()->getData($key);
    }


}
