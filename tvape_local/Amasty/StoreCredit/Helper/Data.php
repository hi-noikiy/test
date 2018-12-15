<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function isModuleActive($storeId = null)
    {
        $storeId = Mage::app()->getStore($storeId)->getId();
        $isActive = Mage::getStoreConfig('amstcred/general/active', $storeId);

        return (bool)$isActive;
    }


    public function getStoreCreditFields()
    {
        $_currencyShortName = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getShortName();

        return array(
            'amstcred_amount' => array('fieldName' => $this->__('Store Credit Value in %s', $_currencyShortName)),
            'amstcred_amount_custom' => array('fieldName' => $this->__('Custom Store Credit Value')),
        );
    }

    public function isAllowedStoreCredit($quote = null, $order = null)
    {

        $isAllowedStoreCredit = true;
        $listAllowedProductTypes = Mage::getStoreConfig('amstcred/general/allowed_product_types');
        if (empty($listAllowedProductTypes)) {
            return false;
        }
        $listAllowedProductTypes = explode(",", $listAllowedProductTypes);

        if (!is_null($order)) {
            $items = $order->getAllItems();
        } elseif (!is_null($quote)) {
            $items = $quote->getAllItems();
        } else {
            $items = Mage::getSingleton('checkout/cart')->getItems();
        }

        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $type = $item->getProduct()->getTypeId();

            // for grouped products
            foreach ($item->getOptions() as $option) {
                if ($option->getCode() == 'product_type') {
                    $type = $option->getValue();
                }
            }
            if (!in_array($type, $listAllowedProductTypes)) {
                $isAllowedStoreCredit = false;
                break;
            }
        }

        return $isAllowedStoreCredit;

    }
}
