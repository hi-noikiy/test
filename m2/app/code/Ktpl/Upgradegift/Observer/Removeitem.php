<?php

namespace Ktpl\Upgradegift\Observer;

use Magento\Framework\Event\ObserverInterface;

class Removeitem implements ObserverInterface {

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
        $item = $observer->getQuoteItem();
        $product = $item->getProduct();
        $quote = $this->_checkoutSession->getQuote();
        
        $map = false;
        
        if(!empty($quote->getGiftMap())) :
            $map = unserialize($quote->getGiftMap());
        endif;    
        
        $productSku = $product->getSku();
        
        if(!empty($map)) : 
        
            foreach($map as $giftProductId => $data)
            {
                if (in_array($productSku, $data['ids'])) 
                {
                    unset($map[$giftProductId]['ids'][array_search($productSku, $data['ids'])]);
                }
                else
                {
                    continue;
                }

                if (count($map[$giftProductId]['ids']) == 0)
                {
                    unset($map[$giftProductId]);
                    $this->removeGiftItem($quote, $giftProductId);
                    continue;
                }
                
                //$qty = $data['qty'] - $this->getItemQtyBySku($quote, $productSku);
                $qty = $data['qty'] - $item->getQty();
                if ($qty > 0)
                {
                    $this->setGiftItemQty($quote, $giftProductId, $qty);
                }
                if ($qty == 0){
                    $this->removeGiftItem($quote, $giftProductId);
                }
            }

            $quote->setGiftMap(serialize($map))->save();
            return $this;
            
        endif;
        
    }

    private function removeGiftItem(\Magento\Quote\Model\Quote $quote, $giftProductId)
    {
        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductId() == $giftProductId) {
                $this->_cart->removeItem($item->getId())->save();
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

}
