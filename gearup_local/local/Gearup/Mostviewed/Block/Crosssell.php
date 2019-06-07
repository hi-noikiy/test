<?php

class Gearup_Mostviewed_Block_Crosssell extends Amasty_Mostviewed_Block_Checkout_Cart_Crosssell {

    public function getItems($case =1 ) {
        $items = $this->getData('items');
        if (!is_null($items)) {
            return $items;
        }

        $alreadyInCartIds = $this->_getCartProductIds();
        if (!$alreadyInCartIds) {
            return parent::getItems();
        }

        if (!Mage::getStoreConfig('ammostviewed/cross_sells/enabled')) {
            return parent::getItems();
        }

        $id = (int) $this->_getLastAddedProductId();
        if (!$id) {
            $id = current($alreadyInCartIds);
        }

        $items = array();
        if (Mage::getStoreConfig('ammostviewed/cross_sells/manually')) {
            $items = Mage::helper('gearup_mostviewed')->getViewedWith($id, 'cross_sells', $alreadyInCartIds,$case);
        } else {
            $items = parent::getItems();
            if (!count($items)) {
                $items = Mage::helper('gearup_mostviewed')->getViewedWith($id, 'cross_sells', $alreadyInCartIds,$case);
            }
        }

        //$this->setData('items', $items);

        return $items;
    }

}
