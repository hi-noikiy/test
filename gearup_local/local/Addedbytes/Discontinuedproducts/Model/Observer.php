<?php

class Addedbytes_Discontinuedproducts_Model_Observer {
    /**
     * Remove attached categories when product is discountinue.
     *
     * @param Varien_Event_Observer
     */
    public function chkDiscontinue($observer){
        /**
         * @var $product Mage_Catalog_Model_Product
         */
        $product = $observer->getEvent()->getProduct();
        if($product->getDiscontinuedProduct() == 1) {
            // $product->setCategoryIds(array());
        }
    }
}