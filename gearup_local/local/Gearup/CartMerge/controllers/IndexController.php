<?php
class Gearup_CartMerge_IndexController extends Mage_Core_Controller_Front_Action
{

    protected function clearLastVisitItems() {
        Mage::getSingleton('core/session')->unsPopupItems();
    }

    public function indexAction()
    {
        $cart = Mage::getSingleton('checkout/cart');
        $items = unserialize(Mage::getSingleton('core/session')->getPopupItems());
        $previousVisit = $this->getRequest()->getPost()['previous-visit'];
        foreach ($items as $index => $item) {
            if (in_array($index, $previousVisit)) {
                $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getStoreId())
                    ->load($item->getProductId());
                $cart->addProduct($product, unserialize($item->getBuyRequest()));

            } else {
                $item->save();
            }
        }
        $cart->save();

        self::clearLastVisitItems();
        $this->_redirectReferer();
    }


    public function saveForLaterAction()
    {
        $items = unserialize(Mage::getSingleton('core/session')->getPopupItems());
        foreach($items as $item){
            $item->save();
        }
        self::clearLastVisitItems();
        $this->_redirectReferer();
    }
}
?>