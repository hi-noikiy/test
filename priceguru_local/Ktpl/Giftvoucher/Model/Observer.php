<?php

class Ktpl_Giftvoucher_Model_Observer {

    /**
     * Apply gift codes to cart
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Checkout_CartController
     */
    public function paymentMethodIsActive(Varien_Event_Observer $observer) {
        $event = $observer->getEvent();
        $method = $event->getMethodInstance();
        $result = $event->getResult();
        $quote = $event->getQuote();
        if ($quote) {
            if ($method->getCode() == 'cashondelivery') {
                foreach ($quote->getAllVisibleItems() as $item) {
                    if ($item->getProductType() == 'giftvoucher'):
                        $result->isAvailable = false;
                    endif;
                }
            }
        }
    }

    public function CheckCartAdd(Varien_Event_Observer $observer) {
        $id = $_REQUEST['product'];
        $product = Mage::getModel('catalog/product')->load($id);
        // $product = $observer->getEvent()->getProduct();
        $quote = Mage::getSingleton('checkout/cart')->getQuote();
        try {
            if ($product->getTypeId() == 'giftvoucher' && $quote) {
                foreach ($quote->getAllVisibleItems() as $item) {
                    if ($item->getProductType() != 'giftvoucher'):
                        $message = 'Please remove the cart item and then add gift voucher.';
                        Mage::throwException('Please remove the cart');
                    endif;
                }
            }
            else if ($product->getTypeId() != 'giftvoucher' && $quote) {
                foreach ($quote->getAllVisibleItems() as $item) {
                    if ($item->getProductType() == 'giftvoucher'):
                        $message = 'Please remove the gift voucher and then add Item.';
                        Mage::throwException('gifterrror');
                    endif;
                }
            }
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($message);
            Mage::app()->getResponse()->setRedirect($product->getProductUrl())->sendResponse();
            exit;
        }
    }

}
