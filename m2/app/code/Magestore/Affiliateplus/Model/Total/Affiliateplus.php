<?php
/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Model\Total;
class Affiliateplus extends \Magestore\Affiliateplus\Model\Total\AbstractTotal
{
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {
        parent::collect($quote, $shippingAssignment, $total);
        if (!$this->_helper->isAffiliateModuleEnabled()) {
            return $this;
        }
        $address = $shippingAssignment->getShipping()->getAddress();
        $items   = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }
        $applyTaxAfterDiscount = (bool)$this->_helperConfig
            ->getConfig(\Magento\Tax\Model\Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $quote->getStoreId());
        if (!$applyTaxAfterDiscount) {
            return $this;
        }
        $discount_include_tax = false;
        if ((int)($this->_helper->getConfig('tax/calculation/discount_tax', $quote->getStore())) == 1) {
            $discount_include_tax = true;
        }
        if ($this->_helperConfig->getDiscountConfig('type_discount') == 'product') {
            $this->_clearSession();
            return $this;
        }
        if ($this->_helperConfig->getDiscountConfig('allow_discount') == 'system') {
            $appliedRuleIds = [];
            if (is_string($address->getAppliedRuleIds())) {
                $appliedRuleIds = explode(',', $address->getAppliedRuleIds());
                $appliedRuleIds = array_filter($appliedRuleIds);
            }
            if (count($appliedRuleIds)) {
                $this->_clearSession();
                return $this;
            }
        }
        $session       = $this->_checkoutSession;
        $orderId       = 0;
        $affiliateInfo = '';
        if ($this->_helper->isAdmin()) {
            $orderId = $this->_backendQuoteSession->getOrder()->getId();
            if ($orderId) {
                $affiliateInfo = $this->_helper->getAffiliateInfoByOrderId($orderId);
            }
        } else {
            $affiliateInfo = $this->_helperCookie->getAffiliateInfo();
        }
//        $dataProcessing = $this->_helper->processDataWhenEditOrder();
//        if (isset($dataProcessing['current_couponcode'])) {
//            $currentCouponCode = $dataProcessing['current_couponcode'];
//        }
//
//        if (isset($dataProcessing['base_affiliate_discount'])) {
//            $baseAffiliateDiscount = $dataProcessing['base_affiliate_discount'];
//        }
//
//        if (isset($dataProcessing['customer_id'])) {
//            $customerId = $dataProcessing['customer_id'];
//        }
//
//        if (isset($dataProcessing['default_discount'])) {
//            $defaultDiscount = $dataProcessing['default_discount'];
//        }
        /* */
        $couponCodeBySession = $session->getAffiliateCouponCode();
        $isAllowUseCoupon    = $this->_helper->isAllowUseCoupon($couponCodeBySession);
        if (!$isAllowUseCoupon || !$this->_helper->isAffiliateModuleEnabled()) {
            $session->unsAffiliateCouponCode();
        }
        $isEnableLiftime = $this->_helperConfig->getCommissionConfig('life_time_sales');
        if ($this->_helperCookie->getNumberOrdered() == 1 && !$session->getData('affiliate_coupon_code') && isset($currentCouponCode) && $currentCouponCode != '') {
            return $this;
        } else if ($isEnableLiftime == 0 && $this->_helperCookie->getNumberOrdered() > 1 && !$session->getData('affiliate_coupon_code') && isset($currentCouponCode) && $currentCouponCode != '') {
            return $this;
        }
        $baseDiscount = 0;
//        $affiliateInfo = $this->_helperCookie->getAffiliateInfo();
        $discountObj = new \Magento\Framework\DataObject(
            [
                'affiliate_info'      => $affiliateInfo,
                'base_discount'       => $baseDiscount,
                'default_discount'    => true,
                'discounted_products' => [],
                'discounted_items'    => [],
                'program'             => '',
            ]
        );
//        if (!isset($customerId)) {
//            $customerId = '';
//        }
        /** add new event to calculate discount in multiple program plugin when edit order */
//        if ($this->_helper->isAdmin()) {
//
//            $this->_eventManager->dispatch('affiliateplus_address_collect_total_edit',
//                [
//                    'address' => $address,
//                    'quote' => $quote,
//                    'shipping_assignment' => $shippingAssignment,
//                    'total' => $total,
//                    'discount_obj' => $discountObj,
//                ]
//            );
//        }
//        /** end add new event  */
//        else {
        $this->_eventManager->dispatch('affiliateplus_address_collect_total',
            [
                'address'             => $address,
                'quote'               => $quote,
                'shipping_assignment' => $shippingAssignment,
                'total'               => $total,
                'discount_obj'        => $discountObj,
            ]
        );
//        }
        $baseDiscount = $discountObj->getBaseDiscount();
        $storeId      = $quote->getStoreId();
        if ($discountObj->getDefaultDiscount()) {
            $account = '';
            if ($affiliateInfo) {
                foreach ($affiliateInfo as $info) {
                    if ((isset($info['account']) && $info['account'])) {
                        $account = $info['account'];
                    }
                }
            }
            if (
                (isset($defaultDiscount) && $defaultDiscount && !$couponCodeBySession && $this->_helper->isAdmin())
//                || (isset($dataProcessing['program_name']) && $dataProcessing['program_name'] == 'Affiliate Program' && $this->_helper->isAdmin())
                || ($discountObj->getProgram() == 'Affiliate Program')
                || ($account && $account->getId())
                || (isset($baseAffiliateDiscount) && $baseAffiliateDiscount)
            ) {
                $discountType  = $this->_helperConfig
                    ->getDiscountConfig('discount_type', $storeId);
                $discountValue = floatval($this->_helperConfig
                    ->getDiscountConfig('discount', $storeId));
                if (($orderId && $this->_helperCookie->getNumberOrdered() > 1)
                    || (!$orderId && $this->_helperCookie->getNumberOrdered())
                ) {
                    if ($this->_helperConfig->getDiscountConfig('use_secondary', $storeId)) {
                        $discountType  = $this->_helperConfig
                            ->getDiscountConfig('secondary_type', $storeId);
                        $discountValue = floatval($this->_helperConfig
                            ->getDiscountConfig('secondary_discount', $storeId));
                    }
                }
                $discountedItems    = $discountObj->getDiscountedItems();
                $discountedProducts = $discountObj->getDiscountedProducts();
                if ($discountValue <= 0) {
                    return $this;
                }
                if ($discountType == 'cart_fixed') {
                    $baseItemsPrice = 0;
                    foreach ($items as $item) {
                        if ($item->getParentItemId()) {
                            continue;
                        }
                        if (in_array($item->getProductId(), $discountedProducts) && $this->_helper->isAdmin()) {
                            continue;
                        }
                        if (in_array($item->getId(), $discountedItems) && !$this->_helper->isAdmin()) {
                            continue;
                        }
                        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                            foreach ($item->getChildren() as $child) {
                                if (!$discount_include_tax) {
                                    $baseItemsPrice += $item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                                } else {
                                    $baseItemsPrice += $item->getQty() * ($child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount());
                                }
                            }
                        } elseif ($item->getProduct()) {
                            if (!$discount_include_tax) {
                                $baseItemsPrice += $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
                            } else {
                                $baseItemsPrice += $item->getQty() * $item->getBasePriceInclTax() - $item->getBaseDiscountAmount();
                            }
                        }
                    }
                    if ($baseItemsPrice) {
                        $totalBaseDiscount = min($discountValue, $baseItemsPrice);
                        foreach ($items as $item) {
                            if ($item->getParentItemId()) {
                                continue;
                            }
                            if (in_array($item->getProductId(), $discountedProducts) && $this->_helper->isAdmin()) {
                                continue;
                            }
                            if (in_array($item->getId(), $discountedItems) && !$this->_helper->isAdmin()) {
                                continue;
                            }
                            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                                foreach ($item->getChildren() as $child) {
                                    if (!$discount_include_tax) {
                                        $price = $item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                                    } else {
                                        $price = $item->getQty() * ($child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount());
                                    }
                                    $childBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                                    $child->setBaseAffiliateplusAmount($childBaseDiscount)
                                        ->setAffiliateplusAmount($this->_abstractTemplate->convertPrice($childBaseDiscount));
                                    /** Caculate discount for hidden tax */
                                    $baseTaxableAmount = $child->getBaseTaxableAmount();
                                    $taxableAmount     = $child->getTaxableAmount();
                                    $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
                                    $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));
                                    $store = $this->_storeManager->getStore()->getId();
                                    if ($this->_objectManager->create('Magento\Tax\Helper\Data')->priceIncludesTax()) {
                                        $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                                        if ($rate > 0) {
                                            $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $child->getBaseTaxableAmount(), $rate));
                                            $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $child->getTaxableAmount(), $rate));
                                        }
                                    }
                                }
                            } elseif ($item->getProduct()) {
                                if (!$discount_include_tax) {
                                    $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
                                } else {
                                    $price = $item->getQty() * $item->getBasePriceInclTax() - $item->getBaseDiscountAmount();
                                }
                                $itemBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                                $item->setBaseAffiliateplusAmount($itemBaseDiscount)
                                    ->setAffiliateplusAmount($this->_abstractTemplate->convertPrice($itemBaseDiscount));
                                /** Caculate discount for hidden tax */
                                $baseTaxableAmount = $item->getBaseTaxableAmount();
                                $taxableAmount     = $item->getTaxableAmount();
                                $item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $item->getBaseAffiliateplusAmount()));
                                $item->setTaxableAmount(max(0, $taxableAmount - $item->getAffiliateplusAmount()));
                                if ($this->_objectManager->create('Magento\Tax\Helper\Data')->priceIncludesTax()) {
                                    $rate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
                                    if ($rate > 0) {
                                        $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $item->getBaseTaxableAmount(), $rate));
                                        $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $item->getTaxableAmount(), $rate));
                                    }
                                }
                            }
                        }
                        $baseDiscount += $totalBaseDiscount;
                    }
                } elseif ($discountType == 'fixed') {
                    foreach ($items as $item) {
                        if ($item->getParentItemId()) {
                            continue;
                        }
                        if (in_array($item->getProductId(), $discountedProducts) && $this->_helper->isAdmin()) {
                            continue;
                        }
                        if (in_array($item->getId(), $discountedItems) && !$this->_helper->isAdmin()) {
                            continue;
                        }
                        $itemBaseDiscount = 0;
                        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                            foreach ($item->getChildren() as $child) {
                                $childBaseDiscount = $item->getQty() * $child->getQty() * $discountValue;
                                if (!$discount_include_tax) {
                                    $price = $item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                                } else {
                                    $price = $item->getQty() * ($child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount());
                                }
                                $childBaseDiscount = ($childBaseDiscount < $price) ? $childBaseDiscount : $price;
                                $itemBaseDiscount += $childBaseDiscount;
                                $child->setBaseAffiliateplusAmount($childBaseDiscount)
                                    ->setAffiliateplusAmount($this->_abstractTemplate->convertPrice($childBaseDiscount));
                                /** Caculate discount for hidden tax */
                                $baseTaxableAmount = $child->getBaseTaxableAmount();
                                $taxableAmount     = $child->getTaxableAmount();
                                $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
                                $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));
                                $store = $this->_storeManager->getStore()->getId();
                                if ($this->_objectManager->create('Magento\Tax\Helper\Data')->priceIncludesTax()) {
                                    $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                                    if ($rate > 0) {
                                        $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $child->getBaseTaxableAmount(), $rate));
                                        $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $child->getTaxableAmount(), $rate));
                                    }
                                }
                            }
                        } elseif ($item->getProduct()) {
                            $itemBaseDiscount = $item->getQty() * $discountValue;
                            if (!$discount_include_tax) {
                                $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
                            } else {
                                $price = $item->getQty() * $item->getBasePriceInclTax() - $item->getBaseDiscountAmount();
                            }
                            $itemBaseDiscount = ($itemBaseDiscount < $price) ? $itemBaseDiscount : $price;
                            $item->setBaseAffiliateplusAmount($itemBaseDiscount)
                                ->setAffiliateplusAmount($this->_abstractTemplate->convertPrice($itemBaseDiscount));
                            /** Caculate discount for hidden tax */
                            $baseTaxableAmount = $item->getBaseTaxableAmount();
                            $taxableAmount     = $item->getTaxableAmount();
                            $item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $item->getBaseAffiliateplusAmount()));
                            $item->setTaxableAmount(max(0, $taxableAmount - $item->getAffiliateplusAmount()));
                            $store = $this->_storeManager->getStore()->getId();
                            if ($this->_objectManager->create('Magento\Tax\Helper\Data')->priceIncludesTax()) {
                                $rate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
                                if ($rate > 0) {
                                    $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $item->getBaseTaxableAmount(), $rate));
                                    $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $item->getTaxableAmount(), $rate));
                                }
                            }
                        }
                        $baseDiscount += $itemBaseDiscount;
                    }
                } else {
                    if ($discountValue > 100) {
                        $discountValue = 100;
                    }
                    if ($discountValue < 0) {
                        $discountValue = 0;
                    }
                    foreach ($items as $item) {
                        if ($item->getParentItemId()) {
                            continue;
                        }
                        if (in_array($item->getProductId(), $discountedProducts) && $this->_helper->isAdmin()) {
                            continue;
                        }
                        if (in_array($item->getId(), $discountedItems) && !$this->_helper->isAdmin()) {
                            continue;
                        }
                        $itemBaseDiscount = 0;
                        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                            foreach ($item->getChildren() as $child) {
                                /*  calculating discount base on incl or excl tax price */
                                if (!$discount_include_tax) {
                                    $price = $item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                                } else {
                                    $price = $item->getQty() * ($child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount());
                                }
                                $childBaseDiscount = $price * $discountValue / 100;
                                $itemBaseDiscount += $childBaseDiscount;
                                $child->setBaseAffiliateplusAmount($childBaseDiscount)
                                    ->setAffiliateplusAmount($this->_abstractTemplate->convertPrice($childBaseDiscount));
                                /** Caculate discount for hidden tax */
                                $baseTaxableAmount = $child->getBaseTaxableAmount();
                                $taxableAmount     = $child->getTaxableAmount();
                                $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
                                $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));
                                $store = $this->_storeManager->getStore()->getId();
                                if ($this->_objectManager->create('Magento\Tax\Helper\Data')->priceIncludesTax()) {
                                    $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                                    if ($rate > 0) {
                                        $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $child->getBaseTaxableAmount(), $rate));
                                        $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $child->getTaxableAmount(), $rate));
                                    }
                                }
                            }
                        } elseif ($item->getProduct()) {
                            /** calculating discount base on incl or excl tax price  */
                            if (!$discount_include_tax) {
                                $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
                            } else {
                                $price = $item->getQty() * $item->getBasePriceInclTax() - $item->getBaseDiscountAmount();
                            }
                            $itemBaseDiscount = $price * $discountValue / 100;
                            $item->setBaseAffiliateplusAmount($itemBaseDiscount)
                                ->setAffiliateplusAmount($this->_abstractTemplate->convertPrice($itemBaseDiscount));
                            /** Caculate discount for hidden tax */
                            $baseTaxableAmount = $item->getBaseTaxableAmount();
                            $taxableAmount     = $item->getTaxableAmount();
                            $item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $item->getBaseAffiliateplusAmount()));
                            $item->setTaxableAmount(max(0, $taxableAmount - $item->getAffiliateplusAmount()));
                            $store = $this->_storeManager->getStore()->getId();
                            if ($this->_objectManager->create('Magento\Tax\Helper\Data')->priceIncludesTax()) {
                                $rate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
                                if ($rate > 0) {
                                    $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $item->getBaseTaxableAmount(), $rate));
                                    $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $item->getTaxableAmount(), $rate));
                                }
                            }
                        }
                        $baseDiscount += $itemBaseDiscount;
                    }
                }
            }
        }
        if ($baseDiscount >= 0) {
            $discount = $this->_abstractTemplate->convertPrice($baseDiscount);
            $total->setBaseAffiliateplusDiscount(-$baseDiscount);
            $total->setAffiliateplusDiscount(-$discount);
            $total->setMagestoreBaseDiscount($baseDiscount);
            $session = $this->getCheckoutSession();
            $session->setData('affiliateplus_discount', -$discount);
            $session->setData('base_affiliateplus_discount', -$baseDiscount);
            if ($discountObj->getProgram()) {
                $session->setProgramData($discountObj->getProgram());
            }
            if ($session->getData('affiliate_coupon_code')) {
                $total->setAffiliateplusCoupon($session->getData('affiliate_coupon_code'));
            }
            if ($this->_helper->isAdmin()) {
                $this->_backendQuoteSession->setData('affiliateplus_discount', -$discount);
                $this->_backendQuoteSession->setData('base_affiliateplus_discount', -$baseDiscount);
                if ($discountObj->getProgram()) {
                    $this->_backendQuoteSession->setProgramData($discountObj->getProgram());
                }
                if ($this->_backendQuoteSession->getData('affiliate_coupon_code')) {
                    $total->setAffiliateplusCoupon($this->_backendQuoteSession->getData('affiliate_coupon_code'));
                }
            }
            $total->addTotalAmount('affiliateplus', -$discount);
            $total->addBaseTotalAmount('affiliateplus', -$baseDiscount);
            $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseDiscount);
            $total->setGrandTotal($total->getGrandTotal() - $discount);
            if ($this->_helperConfig->getDiscountConfig('allow_discount') == 'affiliate') {
                $total->setDiscountAmount(0);
                $total->setBaseDiscountAmount(0);
            }
            /**
             * buy product via paypal by quote
             */
            $quote->setBaseAffiliateplusDiscount($total->getBaseAffiliateplusDiscount());
            $quote->setAffiliateplusDiscount($total->getAffiliateplusDiscount());
        }
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote,
                          \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result                = null;
        $applyTaxAfterDiscount = (bool)$this->_helperConfig
            ->getConfig(\Magento\Tax\Model\Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $quote->getStoreId());
        if (!$applyTaxAfterDiscount) {
            return $result;
        }
        $session = $this->getCheckoutSession();
        $orderId = $this->getQuoteSession()->getOrder()->getId();
        if (!$orderId) {
            $orderId = '';
        }
        /** show affiliate discount  */
        if (isset($orderId) && $this->_helper->isAdmin()) {
            $this->_helper->showAffiliateDiscount($orderId);
        }
        $amount = $quote->getAffiliateplusDiscount();
        $title  = __('Affiliate Discount');
        if ($amount != 0) {
            if ($total->getAffiliateplusCoupon()) {
                $title .= ' (' . $total->getAffiliateplusCoupon() . ')';
            } /** show coupon code when edit Order  */
            else if ($session->getData('affiliate_coupon_code')) {
                $title .= ' (' . $session->getData('affiliate_coupon_code') . ')';
            }/** end show coupon code   */;
            $result = [
                'code'  => 'affiliateplus',
                'title' => $title,
                'value' => $amount,
            ];
        }
        return $result;
    }

    /**
     * @param $address
     * @param $product
     * @param $store
     * @return int
     */
    public function getItemRateOnQuote($address,
                                       $product,
                                       $store)
    {
        $taxClassId = $product->getTaxClassId();
        if ($taxClassId) {
            $request = $this->getCaculationTaxModel()->getRateRequest(
                $address,
                $address->getQuote()->getBillingAddress(),
                $address->getQuote()->getCustomerTaxClassId(),
                $store
            );
            $rate    = $this->getCaculationTaxModel()
                ->getRate($request->setProductClassId($taxClassId));
            return $rate;
        }
        return 0;
    }

    /**
     * @param $address
     * @param $store
     * @return mixed
     */
    public function getShipingTaxRate($address,
                                      $store)
    {
        $request = $this->getCaculationTaxModel()->getRateRequest(
            $address,
            $address->getQuote()->getBillingAddress(),
            $address->getQuote()->getCustomerTaxClassId(),
            $store
        );
        $request->setProductClassId($this->_objectManager->create('Magento\Tax\Model\Config')->getShippingTaxClass($store));
        $rate = $this->getCaculationTaxModel()->getRate($request);
        return $rate;
    }

    /**
     * @param $price
     * @param $rate
     * @return mixed
     */
    public function calTax($price,
                           $rate)
    {
        return $this->round($this->getCaculationTaxModel()->calcTaxAmount($price, $rate, true, false));
    }

    /**
     * @param $price
     * @return mixed
     */
    public function round($price)
    {
        return $this->getCaculationTaxModel()->round($price);
    }

    /**
     * @param Address\Total $total
     */
    protected function clearValues(Address\Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }

    /**
     * Clear session before calculating discount
     */
    protected function _clearSession()
    {
        $session = $this->getCheckoutSession();
        $session->setData('affiliateplus_discount', 0);
        $session->setData('base_affiliateplus_discount', 0);
        $session->setProgramData(null);
        if ($this->_helper->isAdmin()) {
            $this->_backendQuoteSession->setData('affiliateplus_discount', 0);
            $this->_backendQuoteSession->setData('base_affiliateplus_discount', 0);
            $this->_backendQuoteSession->setProgramData(null);
        }
    }
}