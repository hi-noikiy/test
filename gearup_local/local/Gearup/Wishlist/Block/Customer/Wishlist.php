<?php

class Gearup_Wishlist_Block_Customer_Wishlist extends Mage_Wishlist_Block_Customer_Wishlist
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()
                      ->createBlock('page/html_pager', 'wishlist.customer.pager')
                      ->setCollection($this->getWishlist());
        $this->setChild('pager', $pager);
        $this->getWishlist()->load();
        return $this;
    }
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }  
}