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


class Credit extends \Magestore\Affiliateplus\Model\Total\AbstractTotal
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
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        if (!$this->_helper->isAffiliateModuleEnabled()){
            return $this;
        }
        $address = $shippingAssignment->getShipping()->getAddress();
        if (!$this->_helperConfig->getPaymentConfig('store_credit')) {
            return $this;
        }
        if ($quote->isVirtual() && $address->getAddressType() == 'shipping') {
            return $this;
        }
        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }
        $discount = 0;
        $session = $this->getCheckoutSession();
        $helper = $this->_helperAccount;
        /**
         * @var \Magestore\Affiliateplus\Model\Session
         */
        $account = $this->_objectManager->create('Magestore\Affiliateplus\Model\Session')->getAccount();

        if ($session->getUseAffiliateCredit() && $helper->isLoggedIn() && !$helper->disableStoreCredit() && $helper->isEnoughBalance()) {
            $balance = $this->_abstractTemplate->convertPrice($helper->getAccount()->getBalance());
            $discount = floatval($session->getAffiliateCredit());
            if ($discount > $balance) {
                $discount = $balance;
            }
//            +$address->getShippingAmount()
            if ($discount > ($address->getSubtotal())) {
                $discount = ($address->getSubtotal());
            }
            if($discount > ($address->getSubtotal() + $address->getShippingAmount())){
                $discount = $address->getSubtotal()+ $address->getShippingAmount();
            }

            if ($discount < 0) {
                $discount = 0;
            }
            $session->setAffiliateCredit($discount);
        } else {
            $session->setUseAffiliateCredit(null);
        }
        $discount_include_tax = false;
        if ((int) ($this->_helper->getConfig('tax/calculation/discount_tax', $quote->getStore())) == 1)
            $discount_include_tax = true;
        if ($discount) {
            $baseItemsPrice = 0;
            $items = $shippingAssignment->getItems();
            if (!count($items)){
                return $this;
            }
            foreach ($items as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        if (!$discount_include_tax){
                            $baseItemsPrice += $item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount() - $child->getRewardpointsBaseDiscount());
                        } else{
                            $baseItemsPrice += $item->getQty() * ($child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount() - $child->getRewardpointsBaseDiscount());
                        }
                    }
                } elseif ($item->getProduct()) {
                    if (!$discount_include_tax){

                        $baseItemsPrice += $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount() - $item->getRewardpointsBaseDiscount();
                    } else{
                        $baseItemsPrice += $item->getQty() * $item->getBasePriceInclTax() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount() - $item->getRewardpointsBaseDiscount();
                    }
                }
            }
            if ($baseItemsPrice) {
                $totalBaseDiscount = min($discount, $baseItemsPrice);
                foreach ($items as $item) {
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                        foreach ($item->getChildren() as $child) {

                            if (!$discount_include_tax){
                                $price = $item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount() - $child->getRewardpointsBaseDiscount());
                            } else{
                                $price = $item->getQty() * ($child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount() - $child->getRewardpointsBaseDiscount());
                            }

                            $childBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                            $child->setBaseAffiliateplusCredit($childBaseDiscount)
                                ->setAffiliateplusCredit($this->_abstractTemplate->convertPrice($childBaseDiscount));
                        }
                    } elseif ($item->getProduct()) {
                        if (!$discount_include_tax){
                            $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount() - $item->getRewardpointsBaseDiscount();
                        } else{
                            $price = $item->getQty() * $item->getBasePriceInclTax() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount() - $item->getRewardpointsBaseDiscount();
                        }

                        $itemBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                        $item->setBaseAffiliateplusCredit($itemBaseDiscount)
                            ->setAffiliateplusCredit($this->_abstractTemplate->convertPrice($itemBaseDiscount));
                    }
                }
            }

            $baseDiscount = $discount / $this->_abstractTemplate->convertPrice(1);

            $session->setData('affiliateplus_credit', -$discount);
            $session->setData('base_affiliateplus_credit', -$baseDiscount);
            $session->setData('account_id', $account->getId());
            $total->setBaseAffiliateCredit(-$baseDiscount);
            $total->setAffiliateCredit(-$discount);
            $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseDiscount);
            $total->setGrandTotal($total->getGrandTotal() - $discount);
            $total->setTotalAmount('affiliatepluscredit', -$discount);
            $total->setBaseTotalAmount('affiliatepluscredit', -$baseDiscount);
            $quote->setAffiliateCredit($total->getAffiliateCredit());
            $quote->setBaseAffiliateCredit($total->getBaseAffiliateCredit());
        }
        return $this;
    }
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        $amount = $total->getAffiliateCredit();
        $title  = __('Paid by Affiliate Credit');
        if($amount!=0){
            $result =  [
                'code' => 'affiliatepluscredit',
                'title' => $title,
                'value' => $amount,
            ];
        }

        return $result;
    }




}