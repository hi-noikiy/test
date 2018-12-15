<?php
class MindMagnet_AddonPopup_IndexController extends Mage_Core_Controller_Front_Action
{

    public function addToCartAction()
    {
        // prepare result
        $result = array();
        $productIdPost = Mage::app()->getRequest()->getParam('product_id');
        $productIdArray = explode('-',$productIdPost);

        $product = Mage::getModel('catalog/product')->load($productIdArray[1]);

        $result['status'] = 'error';
        $result['message'] = 'An error was occurred adding '.$product->getName().' to shopping cart';

        if ($product && $product->getTypeId() == 'simple') {

            $cart = Mage::getModel('checkout/cart');
            $cart->init();
            $cart->addProduct($product, array('qty' => 1 ,'from_addon_popup' => true));
            $cart->save();
            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);

            $result['status'] = 'success';
            $result['message'] = $product->getName().' was added to your cart';
        }

        echo json_encode($result);
    }
}