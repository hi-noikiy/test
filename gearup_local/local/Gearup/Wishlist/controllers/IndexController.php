<?php

require_once Mage::getModuleDir('controllers', 'Mage_Wishlist') . DS . 'IndexController.php';

class Gearup_Wishlist_IndexController extends Mage_Wishlist_IndexController
{
    
    protected function _addItemToWishList()
    {
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            return $this->norouteAction();
        }

        $session = Mage::getSingleton('customer/session');

        $productId = (int)$this->getRequest()->getParam('product');
        if (!$productId) {
            $this->_redirect('*/');
            return;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $session->addError($this->__('Cannot specify product.'));
            $this->_redirect('*/');
            return;
        }

        try {
            $requestParams = $this->getRequest()->getParams();
            if ($session->getBeforeWishlistRequest()) {
                $requestParams = $session->getBeforeWishlistRequest();
                $session->unsBeforeWishlistRequest();
            }
            $buyRequest = new Varien_Object($requestParams);

            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                Mage::throwException($result);
            }
            $wishlist->save();

            Mage::dispatchEvent(
                'wishlist_add_product',
                array(
                    'wishlist' => $wishlist,
                    'product' => $product,
                    'item' => $result
                )
            );

            $referer = $session->getBeforeWishlistUrl();
            if ($referer) {
                $session->setBeforeWishlistUrl(null);
            } else {
                $referer = $this->_getRefererUrl();
            }

            /**
             *  Set referer to avoid referring to the compare popup window
             */
            $session->setAddActionReferer($referer);

            Mage::helper('wishlist')->calculate();

            $productName = $product->getName();
            if (strlen($productName) > 70 && Mage::getDesign()->getPackageName() == "aw_mobile3") {
                $productName = mb_substr($productName, 0, 70, "UTF-8")."...";
            }
            $message = $this->__('<span>%1$s</span>has been added to your wishlist.<br/><a href="%2$s">Click here</a> to continue shopping.',
                $productName, Mage::helper('core')->escapeUrl($referer));
            $session->addSuccess($message);
        } catch (Mage_Core_Exception $e) {
            $session->addError($this->__('An error occurred while adding item to wishlist: %s', $e->getMessage()));
        }
        catch (Exception $e) {
            $session->addError($this->__('An error occurred while adding item to wishlist.'));
        }

        $this->_redirect('*', array('wishlist_id' => $wishlist->getId()));
    }

}
