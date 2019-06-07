<?php

include_once("Mage/Adminhtml/controllers/Catalog/ProductController.php");
class Gearup_Sds_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController
{

    public function saveAction()
    {
        $storeId        = $this->getRequest()->getParam('store');
        $redirectBack   = $this->getRequest()->getParam('back', false);
        $productId      = $this->getRequest()->getParam('id');
        $isEdit         = (int)($this->getRequest()->getParam('id') != null);

        $data = $this->getRequest()->getPost();
        if ($data) {
            $this->_filterStockData($data['product']['stock_data']);

            $product = $this->_initProductSave();

            try {
                $product->save();
                $productId = $product->getId();

                if (isset($data['copy_to_stores'])) {
                   $this->_copyAttributesBetweenStores($data['copy_to_stores'], $product);
                }

                $this->_getSession()->addSuccess($this->__('The product has been saved.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage())
                    ->setProductData($data);
                $redirectBack = true;
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            }
        }

        if ($redirectBack) {
            if ($this->getRequest()->getParam('sds')) {
                $this->_redirect('*/*/edit', array(
                    'id'    => $productId,
                    '_current'=>true,
                    'sds'=>1
                ));
            } else {
                $this->_redirect('*/*/edit', array(
                    'id'    => $productId,
                    '_current'=>true
                ));
            }
            
        } elseif($this->getRequest()->getParam('popup')) {
            $this->_redirect('*/*/created', array(
                '_current'   => true,
                'id'         => $productId,
                'edit'       => $isEdit
            ));
        } else {
            if ($this->getRequest()->getParam('sds')) {
                $this->_redirect('*/sds_sds/', array('store'=>$storeId));
            } else {
                $this->_redirect('*/*/', array('store'=>$storeId));
            }
        }
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $product = Mage::getModel('catalog/product')
                ->load($id);
            $sku = $product->getSku();
            try {
                $product->delete();
                $this->_getSession()->addSuccess($this->__('The product has been deleted.'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        if ($this->getRequest()->getParam('sds')) {
            $this->getResponse()
                ->setRedirect($this->getUrl('*/sds_sds/', array('store'=>$this->getRequest()->getParam('store'))));
        } else {
            $this->getResponse()
                ->setRedirect($this->getUrl('*/*/', array('store'=>$this->getRequest()->getParam('store'))));
        }
    }
}