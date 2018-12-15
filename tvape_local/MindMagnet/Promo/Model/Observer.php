<?php
class MindMagnet_Promo_Model_Observer
{
    private $updatedIdsArray = array();

    /**
     * Add gift product to current quote
     *
     * @param Varien_Event_Observer $observer
     */
    public function onCheckoutCartProductAddAfter(Varien_Event_Observer $observer)
    {
        $params = Mage::app()->getRequest()->getParams();
        $rules = Mage::getResourceModel('salesrule/rule_collection')->load();
        $productGiftId = Mage::app()->getRequest()->getParam('radio-group-gift');
        $product = Mage::getModel('catalog/product')->load($productGiftId);
        $upgradeProductId = isset($params['upgrade-product-id'])?$params['upgrade-product-id']:"";
        
        if ($product->getId())
        {
            $limits  = Mage::getSingleton('ampromo/registry')->getLimits();
            $sku = $product->getSku();
            $addAllRule = isset($limits[$sku]) && $limits[$sku] > 0;
            $addOneRule = false;
            $mainProductSku = $observer->getProduct()->getSku();

            if (!$addAllRule)
            {
                foreach ($rules as $rule) 
                {
                    if ($rule->getSimpleAction() == 'ampromo_items') 
                    {
                        $skuArray = explode(",",$rule->getPromoSku());
                        if (in_array($sku,$skuArray))
                        {
                            $addOneRule = $rule->getId();
                            break;
                        }
                    }
                }
            }
            else if (isset($limits[$sku]))
            {
                $addOneRule = $limits[$sku]['rule_id'];
            }

            if ($addAllRule || $addOneRule)
            {
                if($upgradeProductId != "")
                {
                    $product = Mage::getModel('catalog/product')->load($upgradeProductId);
                    if(!$product->getId())
                    {
                        $product = Mage::getModel('catalog/product')->load($productGiftId);
                    }
                }
                
                $super = Mage::app()->getRequest()->getParam('super_attributes');
                $options = Mage::app()->getRequest()->getParam('options');
                $bundleOptions = Mage::app()->getRequest()->getParam('bundle_option');
                $downloadableLinks = Mage::app()->getRequest()->getParam('links');

                /* To compatibility amgiftcard module */
                $amgiftcardValues = array();

                if($product->getTypeId() == 'amgiftcard')
                {
                    $amgiftcardFields = array_keys(Mage::helper('amgiftcard')->getAmGiftCardFields());
                    foreach($amgiftcardFields as $amgiftcardField)
                    {
                        if($this->getRequest()->getParam($amgiftcardField))
                        {
                            $amgiftcardValues[$amgiftcardField] = $this->getRequest()->getParam($amgiftcardField);
                        }
                    }
                }
                
                if($upgradeProductId != "")
                {
                    $cart = Mage::getModel('checkout/cart')->addProduct($product, $params);
                    $this->setToMap($mainProductSku, $upgradeProductId);
                }
                else
                {
                    Mage::helper('ampromo')->addProduct($product, $super, $options, $bundleOptions, $addOneRule, $amgiftcardValues, 1, $downloadableLinks, $params);
                    $this->setToMap($mainProductSku, $productGiftId);
                }
            }
        }
    }

    /**
     * Create map for product and associated gift
     *
     * @param string $productSku
     * @param int $giftProductId
     * @return MindMagnet_Promo_Model_Observer
     */
    private function setToMap($productSku, $giftProductId)
    {
        $cart = Mage::getSingleton('checkout/cart')->getQuote();
        $map = unserialize($cart->getGiftMap());
        $map[$giftProductId]['qty'] = $this->getGiftItemQty($cart, $giftProductId);
        if (!isset($map[$giftProductId]['ids']) || !in_array($productSku, $map[$giftProductId]['ids'])) {
            $map[$giftProductId]['ids'][] = $productSku;
        }
        $cart->setGiftMap(serialize($map))->save();
        return $this;
    }

    /**
     * Event observer remove product from cart
     *
     * @param Varien_Event_Observer $observer
     * @return $this MindMagnet_Promo_Model_Observer
     */
    public function removeProductFromMap($observer)
    {
        $item = $observer->getQuoteItem();
        $product = $item->getProduct();
        $quote = Mage::getSingleton('checkout/cart')->getQuote();
        $map = unserialize($quote->getGiftMap());
        $productSku = $product->getSku();

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

    }


    private function removeGiftItem($quote, $giftProductId)
    {
        $cartHelper = Mage::helper('checkout/cart');
        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductId() == $giftProductId) {
                $cartHelper->getCart()->removeItem($item->getId())->save();
            }
        }
        return 0;
    }

    public function updateGiftQuantities(Varien_Event_Observer $observer)
    {

        $cart = $observer->getEvent()->getCart();

        $quote = $cart->getQuote();

        $map = unserialize($quote->getGiftMap());

        $giftsPerItem = $this->getGiftsPerItem($map);


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
    }

    private function getItemQtyBySku(Mage_Sales_Model_Quote $quote, $productSku)
    {
        foreach ($quote->getAllItems() as $item) {
            if ($item->getSku() == $productSku) {
                return $item->getQty();
            }
        }
        return 0;
    }

    private function getGiftItemQty(Mage_Sales_Model_Quote $quote, $giftId)
    {
        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductId() == $giftId) {
                return $item->getQty();
            }
        }
        return 0;
    }

    private function setGiftItemQty(Mage_Sales_Model_Quote $quote, $giftId, $qty)
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