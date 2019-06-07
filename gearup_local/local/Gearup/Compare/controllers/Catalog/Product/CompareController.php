<?php
require_once "Mage/Catalog/controllers/Product/CompareController.php";  
class Gearup_Compare_Catalog_Product_CompareController extends Mage_Catalog_Product_CompareController{

    public function removeAction()
    {
        $data['error'] = 1;
        if ($productId = (int) $this->getRequest()->getParam('product')) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);

            if($product->getId()) {
                /** @var $item Mage_Catalog_Model_Product_Compare_Item */
                $item = Mage::getModel('catalog/product_compare_item');
                if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $item->addCustomerData(Mage::getSingleton('customer/session')->getCustomer());
                } elseif ($this->_customerId) {
                    $item->addCustomerData(
                        Mage::getModel('customer/customer')->load($this->_customerId)
                    );
                } else {
                    $item->addVisitorId(Mage::getSingleton('log/visitor')->getId());
                }

                $item->loadByProduct($product);

                if($item->getId()) {
                    $item->delete();
                   /* Mage::getSingleton('catalog/session')->addSuccess(
                        $this->__('The product %s has been removed from comparison list.', $product->getName())
                    ); */
                    Mage::dispatchEvent('catalog_product_compare_remove_product', array('product'=>$item));
                    Mage::helper('catalog/product_compare')->calculate();
                    $data['error'] = 0;
                    $data['message'] = $this->__('This item has been removed from <a href="%s">Comparator</a>.', Mage::helper('catalog/product_compare')->getListUrl());
                    $data['count'] = Mage::getSingleton('catalog/session')->getCatalogCompareItemsCount();
                    $data['com'] = $this->getLayout()->createBlock('core/template')->setTemplate('catalog/product/compare/minicompare.phtml')->toHtml();
                }
            }
        }
        
        if (!$this->getRequest()->getParam('isAjax', false)) {
            $this->_redirectReferer();
        }
        
        if ($this->getRequest()->getParam('isAjax', false)) {
            echo json_encode($data); exit;
        }
    }
    
    /**
     * Add item to compare list
     */
    public function addAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirectReferer();
            return;
        }
        $data['error'] = 1;
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId
            && (Mage::getSingleton('log/visitor')->getId() || Mage::getSingleton('customer/session')->isLoggedIn())
        ) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);

            if ($product->getId()/* && !$product->isSuper()*/) {
                Mage::getSingleton('catalog/product_compare_list')->addProduct($product);
                /*Mage::getSingleton('catalog/session')->addSuccess(
                    $this->__('The product %s has been added to comparison list.', Mage::helper('core')->escapeHtml($product->getName()))
                );*/
                Mage::dispatchEvent('catalog_product_compare_add_product', array('product'=>$product));
                $data['error'] = 0;
                $data['message'] = $this->__('This item has been successfully added to <a href="%s">Comparator</a>.', Mage::helper('catalog/product_compare')->getListUrl());
                $data['count'] = Mage::getSingleton('catalog/session')->getCatalogCompareItemsCount();
            }

            Mage::helper('catalog/product_compare')->calculate();
        }
        $data['com'] = $this->getLayout()->createBlock('core/template')->setTemplate('catalog/product/compare/minicompare.phtml')->toHtml();
        if ($this->getRequest()->getParam('isAjax', false)) {
            echo json_encode($data); exit;
        }

        $this->_redirectReferer();
    }


}
				