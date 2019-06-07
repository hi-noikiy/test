<?php
/**
 * Rounding Helper
 */

class Mish_Rounding_Helper_Product_Type_Composite extends Mage_Catalog_Helper_Product_Type_Composite
{
   /**
     * Prepare product specific params to be used in getJsonConfig()
     * @see Mage_Catalog_Block_Product_View::getJsonConfig()
     * @see Mage_ConfigurableSwatches_Block_Catalog_Product_List_Price::getJsonConfig()
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function prepareJsonProductConfig($product)
    {
        $_request = Mage::getSingleton('tax/calculation')->getDefaultRateRequest();
        $_request->setProductClassId($product->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($product->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);
        $taxOnPrice = 0;
        $_regularPrice = $product->getPrice();
        $_finalPrice = $product->getFinalPrice();
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $_priceInclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, true,
                null, null, null, null, null, false);
            $_priceExclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, false,
                null, null, null, null, null, false);
            $taxOnPrice = Mage::getSingleton('tax/calculation')->calcTaxAmount($_priceExclTax, $product->getTaxPercent(), false, true,true);
        } else {
            $_priceInclTax = Mage::helper('tax')->getPrice($product, $_finalPrice, true);
            $_priceExclTax = Mage::helper('tax')->getPrice($product, $_finalPrice);
        }
        $_tierPrices = array();
        $_tierPricesInclTax = array();
        foreach ($product->getTierPrice() as $tierPrice) {
            $_tierPrices[] = Mage::helper('core')->currency(
                Mage::helper('tax')->getPrice($product, (float)$tierPrice['website_price'], false) - $_priceExclTax
                , false, false);
            $_tierPricesInclTax[] = Mage::helper('core')->currency(
                Mage::helper('tax')->getPrice($product, (float)$tierPrice['website_price'], true) - $_priceInclTax
                , false, false);
        }

        return array(
            'productId'           => $product->getId(),
            'productPrice'        => Mage::helper('core')->currency($_finalPrice, false, false),
            'productOldPrice'     => Mage::helper('core')->currency($_regularPrice, false, false),
            'priceInclTax'        => ($taxOnPrice>0)?Mage::helper('core')->currency($_priceExclTax, false, false)+Mage::helper('core')->currency($taxOnPrice, false, false):Mage::helper('core')->currency($_priceInclTax, false, false),
            'priceExclTax'        => Mage::helper('core')->currency($_priceExclTax, false, false),
            'skipCalculate'       => ($_priceExclTax != $_priceInclTax ? 0 : 1),
            'defaultTax'          => $defaultTax,
            'currentTax'          => $currentTax,
            'tierPrices'          => $_tierPrices,
            'tierPricesInclTax'   => $_tierPricesInclTax,
            'swatchPrices'        => $product->getSwatchPrices(),
        );
    }

} 