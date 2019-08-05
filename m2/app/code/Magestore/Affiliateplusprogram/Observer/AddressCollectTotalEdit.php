<?php
/**
 * Magestore
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
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Affiliateplusprogram\Observer;

use Magento\Framework\Event\ObserverInterface;

class AddressCollectTotalEdit extends AbtractObserver implements ObserverInterface
{
    /**
     * @var Magento\Tax\Helper\Data
     */
    protected $_taxHelper;
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
//        if (!$this->_helper->isPluginEnabled()) {
//            return;
//        }
//        $orderId = $this->_backendSessionQuote->getOrder()->getId();
//        $programId = '';
//        $discount_include_tax = false;
//        if ((int) ($this->_cookieHelper->getConfig('tax/calculation/discount_tax')))
//            $discount_include_tax = true;
//        $storeId = $this->_backendSessionQuote->getStore()->getId();
//        $couponCodeBySession = $this->_checkoutSession->getAffiliateCouponCode();
//        $address = $observer->getEvent()->getAddress();
//        $quote = $address->getQuote();
//        $applyTaxAfterDiscount = (bool) $this->_cookieHelper(
//            \Magento\Tax\Model\Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $quote->getStoreId()
//        );
//        $discountObj = $observer->getEvent()->getDiscountObj();
//        $allInfo = $this->_objectManager->get('Magestore\Affiliateplus\Helper\Data')->processDataWhenEditOrder();
//        $account = null;
//        if (count($allInfo)) {
//            if (isset($allInfo['account_info']))
//                $account = $allInfo['account_info'];
//            if (isset($allInfo['program_id']))
//                $programId = $allInfo['program_id'];
//        }
//        if (!$this->_helper->isPluginEnabled()) {
//            if ($programId != '' && $programId != 0) {
//                $session = $this->_checkoutSession;
//                $session->unsAffiliateCouponCode();
//            }
//            return;
//        }
//        if (isset($allInfo['program_name']) && $allInfo['program_name'] == 'Affiliate Program' && $account && $programId == 0) {
//            $discountObj->setProgram('Affiliate Program');
//            return $this;
//        }
//        if (!$account) {
//            $discountObj->setDefaultDiscount(false);
//            return $this;
//        }
//        $items = $this->_backendSessionQuote->getQuote()->getAllVisibleItems();
//        $baseDiscount = $discountObj->getBaseDiscount();
//        $discountedItems = $discountObj->getDiscountedItems();
//        $discountedProducts = $discountObj->getDiscountedProducts();
//        $program = $this->_programFactory->create()
//            ->setStoreId($storeId)
//            ->load($programId);
//        if ($program->getId() && $program->getStatus() != 1) {
//            $session = $this->_checkoutSession;
//            $session->unsAffiliateCouponCode();
//            $program = $this->_helper->getProgramByMaxPriority($account->getId());
//            if (!$program)
//                return $this;
//        }
//        foreach ($items as $item) {
//            if ($item->getParentItemId()) {
//                continue;
//            }
//            if ($account->getUsingCoupon() || ($couponCodeBySession && $account->getId())) {
//                if ($programId != 0)
//                    $discountObj->setDefaultDiscount(false);
//                if (!$program->validateItem($item))
//                    continue;
//            } else {
//                $program = $this->_helper->getProgramByItemAccount($item, $account);
//            }
//            if ($program) {
//                $discountValue = floatval($program->getDiscount());
//                $discountType = $program->getDiscountType();
//                if (($orderId && $this->_cookieHelper->getNumberOrdered() > 1) || (!$orderId && $this->_cookieHelper->getNumberOrdered())) {
//                    if ($program->getSecDiscount()) {
//                        $discountType = $program->getSecDiscountType();
//                        $discountValue = floatval($program->getSecondaryDiscount());
//                    }
//                }
//                if ($discountType == 'cart_fixed') {
//                    $baseItemsPrice = 0;
//                    foreach ($address->getAllItems() as $_item) {
//                        if ($_item->getParentItemId()) {
//                            continue;
//                        }
//                        if (in_array($_item->getId(), $discountedItems)) {
//                            continue;
//                        }
//                        if (!$program->validateItem($_item)) {
//                            continue;
//                        }
//                        if (!$couponCodeBySession && $this->_helper->checkItemInHigherPriorityProgram($account->getId(), $_item, $program->getPriority())) {
//                            continue;
//                        }
//
//                        if ($_item->getHasChildren() && $_item->isChildrenCalculated()) {
//                            foreach ($_item->getChildren() as $child) {
//                                $baseItemsPrice += $_item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
//                            }
//                        } elseif ($_item->getProduct()) {
//                            $baseItemsPrice += $_item->getQty() * $_item->getBasePrice() - $_item->getBaseDiscountAmount();
//                        }
//                    }
//                    if ($baseItemsPrice) {
//                        $totalBaseDiscount = min($discountValue, $baseItemsPrice);
//                        foreach ($address->getAllItems() as $_item) {
//                            if ($_item->getParentItemId()) {
//                                continue;
//                            }
//                            if (in_array($_item->getId(), $discountedItems)) {
//                                continue;
//                            }
//                            if (!$program->validateItem($_item)) {
//                                continue;
//                            }
//                            if (!$couponCodeBySession && $this->_cookieHelper->checkItemInHigherPriorityProgram($account->getId(), $_item, $program->getPriority())) {
//                                continue;
//                            }
//
//                            if ($_item->getHasChildren() && $_item->isChildrenCalculated()) {
//                                foreach ($_item->getChildren() as $child) {
//                                    $price = $_item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
//                                    $childBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
//                                    $child->setBaseAffiliateplusAmount($childBaseDiscount)
//                                        ->setAffiliateplusAmount($this->_storeManager->getStore()->convertPrice($childBaseDiscount));
//                                    if ($applyTaxAfterDiscount) {
//
//                                        $baseTaxableAmount = $child->getBaseTaxableAmount();
//                                        $taxableAmount = $child->getTaxableAmount();
//                                        $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
//                                        $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));
//
//                                        if ($this->getTaxHelper()->priceIncludesTax()) {
//                                            $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
//                                            if ($rate > 0) {
//                                                $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($child->getBaseTaxableAmount(), $rate));
//                                                $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($child->getTaxableAmount(), $rate));
//                                            }
//                                        }
//                                    }
//                                }
//                            } elseif ($_item->getProduct()) {
//                                $price = $_item->getQty() * $_item->getBasePrice() - $_item->getBaseDiscountAmount();
//                                $itemBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
//                                $_item->setBaseAffiliateplusAmount($itemBaseDiscount)
//                                    ->setAffiliateplusAmount($this->_storeManager->getStore()->convertPrice($itemBaseDiscount));
//                                if ($applyTaxAfterDiscount) {
//                                    $baseTaxableAmount = $_item->getBaseTaxableAmount();
//                                    $taxableAmount = $_item->getTaxableAmount();
//                                    $_item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $_item->getBaseAffiliateplusAmount()));
//                                    $_item->setTaxableAmount(max(0, $taxableAmount - $_item->getAffiliateplusAmount()));
//
//                                    if ($this->getTaxHelper()->priceIncludesTax()) {
//                                        $rate = $this->getItemRateOnQuote($address, $_item->getProduct(), $store);
//                                        if ($rate > 0) {
//                                            $_item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($_item->getBaseTaxableAmount(), $rate));
//                                            $_item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($_item->getTaxableAmount(), $rate));
//                                        }
//                                    }
//                                }
//                            }
//                            $discountedItems[] = $_item->getId();
//                            $discountedProducts[] = $_item->getProductId();
//                        }
//                        $baseDiscount += $totalBaseDiscount;
//                    } else {
//                        $discountedItems[] = $item->getId();
//                        $discountedProducts[] = $item->getProductId();
//                    }
//                } elseif ($discountType == 'fixed') {
//                    $itemBaseDiscount = 0;
//                    if ($item->getHasChildren() && $item->isChildrenCalculated()) {
//                        foreach ($item->getChildren() as $child) {
//                            $childBaseDiscount = $item->getQty() * $child->getQty() * $discountValue;
//                            $price = $item->getQty() * ( $child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount() );
//                            $childBaseDiscount = ($childBaseDiscount < $price) ? $childBaseDiscount : $price;
//                            $itemBaseDiscount += $childBaseDiscount;
//                            $child->setBaseAffiliateplusAmount($childBaseDiscount)
//                                ->setAffiliateplusAmount($this->_storeManager->getStore()->convertPrice($childBaseDiscount));
//                            if ($applyTaxAfterDiscount) {
//
//                                $baseTaxableAmount = $child->getBaseTaxableAmount();
//                                $taxableAmount = $child->getTaxableAmount();
//                                $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
//                                $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));
//
//                                if ($this->getTaxHelper()->priceIncludesTax()) {
//                                    $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
//                                    if ($rate > 0) {
//                                        $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($child->getBaseTaxableAmount(), $rate));
//                                        $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($child->getTaxableAmount(), $rate));
//                                    }
//                                }
//                            }
//                        }
//                    } else {
//                        $itemBaseDiscount = $item->getQty() * $discountValue;
//                        $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
//                        $itemBaseDiscount = ($itemBaseDiscount < $price) ? $itemBaseDiscount : $price;
//                        $item->setBaseAffiliateplusAmount($itemBaseDiscount)
//                            ->setAffiliateplusAmount($this->_storeManager->getStore()->convertPrice($itemBaseDiscount));
//
//                        if ($applyTaxAfterDiscount) {
//                            $baseTaxableAmount = $item->getBaseTaxableAmount();
//                            $taxableAmount = $item->getTaxableAmount();
//                            $item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $item->getBaseAffiliateplusAmount()));
//                            $item->setTaxableAmount(max(0, $taxableAmount - $item->getAffiliateplusAmount()));
//
//                            if ($this->getTaxHelper()->priceIncludesTax()) {
//                                $rate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
//                                if ($rate > 0) {
//                                    $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($item->getBaseTaxableAmount(), $rate));
//                                    $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($item->getTaxableAmount(), $rate));
//                                }
//                            }
//                        }
//                    }
//                    if ($itemBaseDiscount > 0) {
//                        $discountedItems[] = $item->getId();
//                        $discountedProducts[] = $item->getProductId();
//                    }
//                    $baseDiscount += $itemBaseDiscount;
//                } elseif ($discountType == 'percentage') {
//                    $itemBaseDiscount = 0;
//                    if ($discountValue > 100)
//                        $discountValue = 100;
//                    if ($discountValue < 0)
//                        $discountValue = 0;
//                    if ($item->getHasChildren() && $item->isChildrenCalculated()) {
//                        foreach ($item->getChildren() as $child) {
//                            if (!$discount_include_tax)
//                                $price = $item->getQty() * ( $child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount() );
//                            else
//                                $price = $item->getQty() * ( $child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount() );
//
//                            $childBaseDiscount = $price * $discountValue / 100;
//                            $itemBaseDiscount += $childBaseDiscount;
//                            $child->setBaseAffiliateplusAmount($childBaseDiscount)
//                                ->setAffiliateplusAmount($this->_helper->convertPrice($childBaseDiscount));
//                            if ($applyTaxAfterDiscount) {
//
//                                $baseTaxableAmount = $child->getBaseTaxableAmount();
//                                $taxableAmount = $child->getTaxableAmount();
//                                $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
//                                $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));
//
//                                if ($this->getTaxHelper()->priceIncludesTax()) {
//                                    $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
//                                    if ($rate > 0) {
//                                        $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($child->getBaseTaxableAmount(), $rate));
//                                        $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($child->getTaxableAmount(), $rate));
//                                    }
//                                }
//                            }
//                        }
//                    } else {
//                        if (!$discount_include_tax)
//                            $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
//                        else
//                            $price = $item->getQty() * $item->getBasePriceInclTax() - $item->getBaseDiscountAmount();
//
//                        $itemBaseDiscount = $price * $discountValue / 100;
//                        $item->setBaseAffiliateplusAmount($itemBaseDiscount)
//                            ->setAffiliateplusAmount($this->_helper->convertPrice($itemBaseDiscount));
//                        if ($applyTaxAfterDiscount) {
//                            $baseTaxableAmount = $item->getBaseTaxableAmount();
//                            $taxableAmount = $item->getTaxableAmount();
//                            $item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $item->getBaseAffiliateplusAmount()));
//                            $item->setTaxableAmount(max(0, $taxableAmount - $item->getAffiliateplusAmount()));
//
//                            if ($this->getTaxHelper()->priceIncludesTax()) {
//                                $rate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
//                                if ($rate > 0) {
//                                    $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($item->getBaseTaxableAmount(), $rate));
//                                    $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($item->getTaxableAmount(), $rate));
//                                }
//                            }
//                        }
//                    }
//                    $discountedItems[] = $item->getId();
//                    $discountedProducts[] = $item->getProductId();
//                    $baseDiscount += $itemBaseDiscount;
//                }
//            }
//        }
//        $discountObj->setDiscountedProducts($discountedProducts);
//        $discountObj->setProgram($program);
//        $discountObj->setBaseDiscount($baseDiscount);
//        $discountObj->setDiscountedItems($discountedItems);
//        return $this;
    }


}