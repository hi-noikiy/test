<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Model_Catalog_Product_Price_StoreCredit extends Mage_Catalog_Model_Product_Type_Price
{
    protected $_minMaxAmount = array();

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getPrice($product)
    {
        if ($product->getData('price')) {
            return $product->getData('price');
        } else {
            return 0;
        }
    }

    /**
     * @param integer $qty
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getFinalPrice($qty = null, $product)
    {
        $finalPrice = $product->getPrice();
        if ($product->hasCustomOptions()) {
            $customOption = $product->getCustomOption('amstcred_amount');
            if ($customOption) {
                $customValue = $customOption->getValue();
                if ($product->getAmstcredPriceType() == Amasty_StoreCredit_Model_StoreCredit::PRICE_TYPE_PERCENT) {
                    $pricePercent = $product->getAmstcredPricePercent();
                    $customValue *= $pricePercent / 100;
                    $customValue = Mage::app()->getStore()->roundPrice($customValue);
                }
                $finalPrice += $customValue;
            }
        }
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);

        $product->setData('final_price', $finalPrice);
        return max(0, $product->getData('final_price'));
    }

    public function getMinMaxAmount($product)
    {
        if (!isset($this->_minMaxAmount[$product->getId()])) {
            $min = $max = null;
            foreach ($this->getAmounts($product) as $amount) {
                $min = is_null($min) ? $amount['value'] : min($min, $amount['value']);
                $max = is_null($max) ? $amount['value'] : max($min, $amount['value']);
            }


            if ($product->getAmstcredAllowOpenAmount()) {
                if (is_null($min)) {
                    $min = (float)$product->getAmstcredOpenAmountMin();
                }

                $min = min($min, $product->getAmstcredOpenAmountMin());


                $max = $product->getAmstcredOpenAmountMax() ? max($max, $product->getAmstcredOpenAmountMax()) : $max;
            }

            $this->_minMaxAmount[$product->getId()] = array('min' => $min, 'max' => $max);
        }
        return $this->_minMaxAmount[$product->getId()];

    }

    public function getAmounts($product)
    {
        $prices = $product->getData('amstcred_amount');

        if (is_null($prices)) {
            if ($attribute = $product->getResource()->getAttribute('amstcred_amount')) {
                $attribute->getBackend()->afterLoad($product);
                $prices = $product->getData('amstcred_amount');
            }
        }

        return ($prices) ? $prices : array();
    }

    public function isMultiAmount($product)
    {
        $minMaxAmount = $this->getMinMaxAmount($product);

        return $minMaxAmount['min'] != $minMaxAmount['max'] || is_null($minMaxAmount['max']);
    }
}
