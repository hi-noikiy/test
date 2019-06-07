<?php

class Redstage_SaveForLater_Helper_Data extends Mage_Core_Helper_Abstract {
    public function count() {
        $items = Mage::getResourceModel('saveforlater/item_collection');

        if( Mage::getSingleton('customer/session')->getCustomer() && Mage::getSingleton('checkout/session')->getQuote()->getId() ){
            $items->getSelect()
                ->where( "
					( quote_id = ". Mage::getSingleton('checkout/session')->getQuote()->getId() ." )
					". ( Mage::getSingleton('customer/session')->getCustomer() ? 'OR ( customer_id = \''. Mage::getSingleton('customer/session')->getCustomer()->getId() .'\')' : '' ) ."
				" );
        } else {
            $items->getSelect()
                ->where( "1 = 2" );
        }

        return count($items);
    }
}