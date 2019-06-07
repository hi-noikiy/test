<?php

class Gearup_Configurator_Model_Product_Type_Price extends Mage_Catalog_Model_Product_Type_Price {

    /**
     * Retrieve product final price
     *
     * @param float|null $qty
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getFinalPrice($qty = null, $product) {
        if (is_null($qty) && !is_null($product->getCalculatedFinalPrice())) {
            return $product->getCalculatedFinalPrice();
        }

        $finalPrice = $this->getBasePrice($product, $qty);
        $product->setFinalPrice($finalPrice);

        Mage::dispatchEvent('catalog_product_get_final_price', array('product' => $product, 'qty' => $qty));

        $finalPrice = $product->getData('final_price');
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);

        $fullActionName = Mage::app()->getFrontController()->getAction()->getFullActionName();
        $code = Mage::app()->getStore()->getCurrentCurrencyCode();
        if ($fullActionName != 'catalog_product_view' && $fullActionName != 'catalog_category_view' && $fullActionName != 'catalogsearch_result_index') {
            if (in_array($code, array('AED')) ) {
                $finalPrice = ceil($finalPrice);
            }
        } 
//        elseif (in_array($code, array('OMR', 'KWD', 'BHD', 'QAR', 'SAR'))) {
//            $finalPrice = round($finalPrice, 1);
//        }
        $finalPrice = max(0, $finalPrice);
        $product->setFinalPrice($finalPrice);

        return $finalPrice;
    }

}
