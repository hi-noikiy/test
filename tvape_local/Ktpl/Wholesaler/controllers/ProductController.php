<?php

require_once 'Mage/Catalog/controllers/ProductController.php';

class Ktpl_Wholesaler_ProductController extends Mage_Catalog_ProductController {

    public function quickViewAction() {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect(Mage::helper('wholesaler')->Wholesalercategoryurl());
        }

        if ($product = $this->_initProduct()) {
            $this->getResponse()
                    ->setBody($this->getLayout()
                            ->createBlock('wholesaler/product')
                            ->setProduct($product)
                            ->toHtml());
        } else {
            echo Mage::helper('catalog')->__('Product not found');
        }
    }

}
