<?php

class EM_Ajaxcart_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function addAction() {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                Mage::getSingleton('checkout/cart')->removeItem($id)
                        ->save();
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addError($this->__('Cannot remove item'));
            }
        }


        if ($this->getRequest()->getParam('product')) {
            $cart = Mage::getSingleton('checkout/cart');
            $params = $this->getRequest()->getParams();
            $related = $this->getRequest()->getParam('related_product');

            $productId = (int) $this->getRequest()->getParam('product');


            if ($productId) {
                $product = Mage::getModel('catalog/product')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->load($productId);
                try {
                    if (!Mage::helper('configurator')->disableAddtoCart($product)) {                       
                        if (!isset($params['qty'])) {
                            $params['qty'] = 1;
                        }

                        $cart->addProduct($product, $params);
                        if (!empty($related)) {
                            $cart->addProductsByIds(explode(',', $related));
                        }
                        $cart->save();

                        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
                        Mage::getSingleton('checkout/session')->setCartInsertedItem($product->getId());

                        $img = '';
                        Mage::dispatchEvent('checkout_cart_add_product_complete', array('product' => $product, 'request' => $this->getRequest()));

                        $message = $this->__('%s was successfully added to your shopping cart.', $product->getName());
                        $img = "<img src='" . Mage::helper('catalog/image')->init($product, 'image')->resize(60, null) . "' />";
                        $tmp_product = '<div class="ajaxcart_image">' . $img . '</div><div class="ajaxcart_message">' . $message . '</div>';

                        Mage::getSingleton('checkout/session')->addSuccess($tmp_product);
                    }else{
                        Mage::register('configurator_error', 'truedsadsa');       
                    }
                    //echo $tmp_product;exit;					
                } catch (Mage_Core_Exception $e) {
                    if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                        Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                    } else {
                        $messages = array_unique(explode("\n", $e->getMessage()));
                        foreach ($messages as $message) {
                            Mage::getSingleton('checkout/session')->addError($message);
                        }
                    }
                } catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addException($e, $this->__('Can not add item to shopping cart'));
                }
            }
        }
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }
    
    public function add2Action() {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                Mage::getSingleton('checkout/cart')->removeItem($id)
                        ->save();
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addError($this->__('Cannot remove item'));
            }
        }


        if ($this->getRequest()->getParam('product')) {
            $cart = Mage::getSingleton('checkout/cart');
            $params = $this->getRequest()->getParams();
            $related = $this->getRequest()->getParam('related_product');

            $productId = (int) $this->getRequest()->getParam('product');


            if ($productId) {
                $product = Mage::getModel('catalog/product')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->load($productId);
                try {
                    $data['error'] = 0;
                    if (!Mage::helper('configurator')->disableAddtoCart($product)) {                       
                        if (!isset($params['qty'])) {
                            $params['qty'] = 1;
                        }

                        $cart->addProduct($product, $params);
                        if (!empty($related)) {
                            $cart->addProductsByIds(explode(',', $related));
                        }
                        $cart->save();

                        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
                        Mage::getSingleton('checkout/session')->setCartInsertedItem($product->getId());

                        $img = '';
                        Mage::dispatchEvent('checkout_cart_add_product_complete', array('product' => $product, 'request' => $this->getRequest()));

                        $message = $this->__('%s was successfully added to your shopping cart.', $product->getName());
                        $img = "<img src='" . Mage::helper('catalog/image')->init($product, 'image')->resize(60, null) . "' />";
                        $tmp_product = '<div class="ajaxcart_image">' . $img . '</div><div class="ajaxcart_message">' . $message . '</div>';

                        Mage::getSingleton('checkout/session')->addSuccess($tmp_product);
                    }else{
                        Mage::register('configurator_error', 'truedsadsa');       
                    }
                    $minicart = $this->getLayout()->createBlock('checkout/cart_sidebar')->setTemplate('checkout/cart/sidebar.phtml')->toHtml(); // <– here’s the block
                    
                    $qty = Mage::helper('checkout/cart')->getSummaryCount();  //get total items in cart
                    $total = Mage::getSingleton('checkout/session')->getQuote()->getSubtotal();
                    $quote = Mage::getSingleton('checkout/session')->getQuote();
                    $quoteItems = $quote->getAllItems();
                    $subtotalInclTax = 0;
                    foreach ($quoteItems as $item) {
                        $subtotalInclTax += $item->getRowTotalInclTax();
                    }
                    $data['price'] =   Mage::helper('checkout')->formatPrice($subtotalInclTax);
                    //$data['price'] = Mage::helper('checkout')->formatPrice(Mage::getSingleton('checkout/cart')->getQuote()->getSubtotal());
                    $data['qty'] = $qty;
                    $data['minicart'] = $minicart;
                    //echo $tmp_product;exit;					
                } catch (Mage_Core_Exception $e) {
                    $data['error'] = 1;
                    $data['message'] = $e->getMessage();
                    if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                        Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                    } else {
                        $messages = array_unique(explode("\n", $e->getMessage()));
                        foreach ($messages as $message) {
                            Mage::getSingleton('checkout/session')->addError($message);
                        }
                    }
                    
                } catch (Exception $e) {
                    $data['error'] = 1;
                    $data['message'] = $e->getMessage();
                    Mage::getSingleton('checkout/session')->addException($e, $this->__('Can not add item to shopping cart'));
                    echo json_encode($data); exit;
                }
            }
            
        }
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
        echo json_encode($data); exit;
    }

    public function addtocartAction() {
        $this->indexAction();
    }

    public function preDispatch() {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
    }

}
