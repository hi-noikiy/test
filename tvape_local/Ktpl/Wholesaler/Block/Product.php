<?php

class Ktpl_Wholesaler_Block_Product extends Mage_Catalog_Block_Product {

    private $product;

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('wholesaler/ajaxproduct.phtml');
    }

    protected function _toHtml() {
        return parent::_toHtml();
    }

    public function setProduct($product) {
        $this->product = $product;
        return $this;
    }

    public function getProduct() {
        return $this->product;
    }
    
    /**
     * 
     * @param type $product
     * @return type
     */
    public function getPriceHtml($product)
    {
        $this->setTemplate('catalog/product/price.phtml');
        $this->setProduct($product);
        return $this->toHtml();
    }

}
