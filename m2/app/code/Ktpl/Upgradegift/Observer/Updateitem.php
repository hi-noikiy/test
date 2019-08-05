<?php

namespace Ktpl\Upgradegift\Observer;

use Magento\Framework\Event\ObserverInterface;

class Updateitem implements ObserverInterface {

    protected $_cart;  
    protected $_checkoutSession;

    public function __construct( 
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Checkout\Model\Cart $cart
    ) 
    {
        $this->_cart = $cart;
        $this->_checkoutSession = $_checkoutSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $cart = $observer->getEvent()->getCart();

        $quote = $cart->getQuote();
        $map = false;

        if(!empty($quote->getGiftMap())) :
            $map = unserialize($quote->getGiftMap());
            $giftsPerItem = $this->getGiftsPerItem($map);
        endif; 
        
        if(!empty($map)) :
            
            foreach($map as $giftProductId => $data)
            {
                $qtyGift = 0;

                foreach ($data['ids'] as $productSku) {

                    if ($giftsPerItem[$productSku] == 1) {
                        $qtyGift += $this->getItemQtyBySku($quote, $productSku);
                    } else {
                        $qtyGift =  (int)$this->getItemQtyBySku($quote, $productSku)/$giftsPerItem[$productSku];
                    }
                }

                if ($qtyGift != 0 && $this->getGiftItemQty($quote, $giftProductId) !== $qtyGift) {

                    $map[$giftProductId]['qty'] = $qtyGift;
                    $this->setGiftItemQty($quote, $giftProductId, $qtyGift);
                }

            }

            $quote->setGiftMap(serialize($map))->save();

            return $this;

        endif;
    }

    private function getItemQtyBySku(\Magento\Quote\Model\Quote $quote, $productSku)
    {
        foreach ($quote->getAllItems() as $item) {
            if ($item->getSku() == $productSku) {
                return $item->getQty();
            }
        }
        return 0;
    }

    private function getGiftItemQty(\Magento\Quote\Model\Quote $quote, $giftId)
    {
        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductId() == $giftId) {
                return $item->getQty();
            }
        }
        return 0;
    }

    private function setGiftItemQty(\Magento\Quote\Model\Quote $quote, $giftId, $qty)
    {
        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductId() == $giftId) {
                $item->setQty($qty);
            }
        }
        return $this;
    }

    private function getGiftsPerItem($map)
    {
        $giftsPerItem = array();

        foreach ($map as $giftId => $data) {
            foreach ($data['ids'] as $productSku) {
                if (isset($giftsPerItem[$productSku])) {
                    $giftsPerItem[$productSku] += 1;
                } else {
                    $giftsPerItem[$productSku] = 1;
                }
            }
        }
        return $giftsPerItem;
    }
    

}
