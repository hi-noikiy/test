<?php
namespace Ktpl\Upgradegift\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class Giftupgrade implements ObserverInterface
{
    protected $_request;
    protected $_product;  
    protected $_promoRegistry;  
    protected $_promoCartHelper;  
    protected $_cart;  
    protected $_checkoutSession;  

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Model\ProductFactory $_product,
        \Amasty\Promo\Model\Registry $promoRegistry,
        \Amasty\Promo\Helper\Cart $promoCartHelper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $_checkoutSession
        
    ) { 
        $this->_request = $request;
        $this->_product = $_product;
        $this->_promoRegistry = $promoRegistry;
        $this->_cart = $cart;
        $this->_checkoutSession = $_checkoutSession;
        $this->_promoCartHelper = $promoCartHelper;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $params = $this->_request->getParams();
        $productGiftId = $this->_request->getParam('radio-group-gift');
        $productgift = $this->_product->create()->load($productGiftId);
        $upgradeProductId = isset($params['radio-gift-update'])?$params['radio-gift-update']:0;
        $allowQty = '';
        /*if($upgradeProductId != 0) 
        {
            $mainProductSku = $observer->getProduct()->getSku();
            $product = $this->_product->create()->load($upgradeProductId);
            /*if(!$product->getId())
            {
                $product = $this->_product->create()->load($productGiftId);
            }*
            $this->_cart->addProduct($product, $params);
            $this->setToMap($mainProductSku, $upgradeProductId);
        } */

        
        if ($productgift->getId())
        {
            $item = [];
            $item['product_id'] = $params['radio-group-gift'];
            $limits = $this->_promoRegistry->getLimits();
            $allRules = $limits;
            $sku = $productgift->getSku();
            $addAllRule = isset($limits[$sku]) && $limits[$sku] > 0;
            $addOneRule = false;
            $mainProductSku = $observer->getProduct()->getSku();


            
             unset($allRules['_groups']);

                if ($allRules) {
                    foreach ($allRules as $ruleId => $rule) {
                        if (isset($rule['sku'][$sku])) {
                            $addAllRule =  $rule['sku'][$sku] > 0;
                            $addOneRule = $ruleId;
                            $allowQty = $rule['sku'][$sku]['qty'];
                            $discount = $rule['sku'][$sku]['discount'];

                            $minimalPrice = $rule['sku'][$sku]['discount']['minimal_price'];
                            break;
                        }
                    }
                }

                if (!$addAllRule && is_array($limits['_groups'])) {
                    foreach ($limits['_groups'] as $ruleId => $rule) {
                        if (in_array($sku, $rule['sku'])) {
                            $addOneRule = $ruleId;
                        }
                        $discount = $rule['discount'];
                        $minimalPrice = $rule['discount']['minimal_price'];
                    }
                }

                if ($addAllRule || $addOneRule) {
                    $qty = $this->getQtyByProductId($productGiftId, $item);
                    if ($allowQty && $qty > $allowQty) {
                        $qty = $allowQty;
                    }

                    $params = $item;
                    $requestOptions = array_intersect_key($params, array_flip([
                        'super_attribute', 'options', 'super_attribute', 'links'
                    ]));
                    if($upgradeProductId != 0)
                    {
                        $productgift = $this->_product->create()->load($upgradeProductId);
                        if(!$productgift->getId())
                        {
                            $productgift = $this->_product->create()->load($productGiftId);
                        }
                        $this->_cart->addProduct($productgift, $params);
                        
                        $this->setToMap($mainProductSku, $upgradeProductId);
                    }
                    else
                    {
                        $this->_promoCartHelper->addProduct(
                            $productgift,
                            $qty,
                            $addOneRule,
                            $requestOptions,
                            $discount,
                            $minimalPrice
                        );
                        $this->setToMap($mainProductSku, $productGiftId);
                }
            }
        } 
    }

    protected function getQtyByProductId($productId, $data)
    {
        return isset($data['ampromo_qty_select_' . $productId])
            ? $data['ampromo_qty_select_'. $productId] : 1;
    }

    private function setToMap($productSku, $giftProductId)
    {
        $cart = $this->_checkoutSession->getQuote();
        $map = unserialize($cart->getGiftMap());
        $map[$giftProductId]['qty'] = $this->getGiftItemQty($cart, $giftProductId);
        if (!isset($map[$giftProductId]['ids']) || !in_array($productSku, $map[$giftProductId]['ids'])) {
            $map[$giftProductId]['ids'][] = $productSku;
        }
        $cart->setGiftMap(serialize($map))->save();
        return $this;
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
}
