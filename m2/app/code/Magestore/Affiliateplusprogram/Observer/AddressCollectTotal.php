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

class AddressCollectTotal extends AbtractObserver implements ObserverInterface
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
        if (!$this->_helper->isPluginEnabled()) {
            return;
        }
        $orderId              = $this->_backendSessionQuote->getOrderId();
        $discount_include_tax = false;
        if ((int)($this->_cookieHelper->getConfig('tax/calculation/discount_tax'))) {
            $discount_include_tax = true;
        }
        $address               = $observer->getEvent()->getAddress();
        $discountObj           = $observer->getEvent()->getDiscountObj();
        $items                 = $address->getAllItems();
        $applyTaxAfterDiscount = (bool)$this->_cookieHelper->getConfig(
            \Magento\Tax\Model\Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $address->getQuote()->getStoreId()
        );
        $affiliateInfo         = $discountObj->getAffiliateInfo();
        $baseDiscount          = $discountObj->getBaseDiscount();
        $discountedItems       = $discountObj->getDiscountedItems();
        $discountedProducts    = $discountObj->getDiscountedProducts();
        $store                 = $this->getStore();
        if (is_array($affiliateInfo) && count($affiliateInfo)) {
            foreach ($affiliateInfo as $info) {
                if ($account = $info['account']) {
                    if ($account->getUsingCoupon()) {
                        $program = $account->getUsingProgram();
                        if (!$program) {
                            return $this;
                        }
                        $storeId = $this->_backendSessionQuote->getStoreId();
                        if ($storeId && !$store->getId())
                            $program = $this->_programFactory->create()
                                ->setStoreId($storeId)
                                ->load($program->getId());
                        $discountObj->setDefaultDiscount(false);
                        if (!$program->validateOrder($address->getQuote()))
                            return $this;
                    }
                    foreach ($items as $item) {
                        if ($item->getParentItemId()) {
                            continue;
                        }
                        if ($account->getUsingCoupon()) {
                            if (!$program->validateItem($item))
                                continue;
                        } else {
                            if (in_array($item->getId(), $discountedItems))
                                continue;
                            $program = $this->_helper->getProgramByItemAccount($item, $account);
                        }
                        if ($program && $program->getStatus() == 1) {
                            $discountType  = $program->getDiscountType();
                            $discountValue = floatval($program->getDiscount());
                            if ($this->_cookieHelper->getNumberOrdered() && !$orderId) {
                                if ($program->getSecDiscount()) {
                                    $discountType  = $program->getSecDiscountType();
                                    $discountValue = floatval($program->getSecondaryDiscount());
                                }
                            } else if ($this->_cookieHelper->getNumberOrdered() > 1 && $orderId) {
                                if ($program->getSecDiscount()) {
                                    $discountType  = $program->getSecDiscountType();
                                    $discountValue = floatval($program->getSecondaryDiscount());
                                }
                            }
                            if ($discountType == \Magestore\Affiliateplus\Model\System\Config\Source\Discounttype::DISCOUNT_FIXED_AMOUNT_PER_CART) {
                                $baseItemsPrice = $this->_getBaseItemsPrice($items, $discountedItems, $program, $discount_include_tax, $account);
                                if ($baseItemsPrice) {
                                    $baseDiscount = $this->_getBaseDiscount2(
                                        $discountValue,
                                        $baseItemsPrice,
                                        $items,
                                        $discountedItems,
                                        $discountedProducts,
                                        $account,
                                        $discount_include_tax,
                                        $applyTaxAfterDiscount,
                                        $address,
                                        $store,
                                        $item,
                                        $baseDiscount,
                                        $program
                                    );
                                } else {
                                    $discountedItems[]    = $item->getId();
                                    $discountedProducts[] = $item->getProductId();
                                }
                            } elseif ($discountType == \Magestore\Affiliateplus\Model\System\Config\Source\Discounttype::DISCOUNT_FIXED_AMOUNT_PER_ITEM) {
                                $baseDiscount = $this->_getBaseDiscount1(
                                    $item,
                                    $discountValue,
                                    $discount_include_tax,
                                    $applyTaxAfterDiscount,
                                    $address,
                                    $store,
                                    $baseDiscount,
                                    $discountedItems,
                                    $discountedProducts

                                );
                            } else {
                                $itemBaseDiscount = 0;
                                if ($discountValue > 100) {
                                    $discountValue = 100;
                                }
                                if ($discountValue < 0) {
                                    $discountValue = 0;
                                }
                                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                                    foreach ($item->getChildren() as $child) {
                                        if (!$discount_include_tax) {
                                            $price = $item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                                        } else {
                                            $price = $item->getQty() * ($child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount());
                                        }
                                        $childBaseDiscount = $price * $discountValue / 100;
                                        $itemBaseDiscount += $childBaseDiscount;
                                        $child->setBaseAffiliateplusAmount($childBaseDiscount)
                                            ->setAffiliateplusAmount($this->_abtractTemplate->convertPrice($childBaseDiscount));
                                        if ($applyTaxAfterDiscount) {
                                            $baseTaxableAmount = $child->getBaseTaxableAmount();
                                            $taxableAmount     = $child->getTaxableAmount();
                                            $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
                                            $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));
                                            if ($this->_priceIncludesTax()) {
                                                $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                                                if ($rate > 0) {
                                                    $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $child->getBaseTaxableAmount(), $rate));
                                                    $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $child->getTaxableAmount(), $rate));
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    if (!$discount_include_tax) {
                                        $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
                                    } else {
                                        $price = $item->getQty() * $item->getBasePriceInclTax() - $item->getBaseDiscountAmount();
                                    }
                                    $itemBaseDiscount = $price * $discountValue / 100;
                                    $item->setBaseAffiliateplusAmount($itemBaseDiscount)
                                        ->setAffiliateplusAmount($this->_abtractTemplate->convertPrice($itemBaseDiscount));
                                    if ($applyTaxAfterDiscount) {
                                        $baseTaxableAmount = $item->getBaseTaxableAmount();
                                        $taxableAmount     = $item->getTaxableAmount();
                                        $item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $item->getBaseAffiliateplusAmount()));
                                        $item->setTaxableAmount(max(0, $taxableAmount - $item->getAffiliateplusAmount()));
                                        if ($this->_priceIncludesTax()) {
                                            $rate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
                                            if ($rate > 0) {
                                                $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $item->getBaseTaxableAmount(), $rate));
                                                $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $item->getTaxableAmount(), $rate));
                                            }
                                        }
                                    }
                                }
                                $discountedItems[]    = $item->getId();
                                $discountedProducts[] = $item->getProductId();
                                $baseDiscount += $itemBaseDiscount;
                            }
                        }
                    }

                    $discountObj->setDiscountedProducts($discountedProducts);
                    $discountObj->setBaseDiscount($baseDiscount);
                    $discountObj->setDiscountedItems($discountedItems);
                    if ($program && $program->getId()) {
                        $discountObj->setProgram($program->getId());
                    }
                    return $this;
                }
            }
            return $this;
        }
    }

    /**
     * @param $items
     * @param $discountedItems
     * @param $program
     * @param $discount_include_tax
     * @param $account
     * @return int
     */
    protected
    function _getBaseItemsPrice($items,
                                $discountedItems,
                                $program,
                                $discount_include_tax,
                                $account)
    {
        $baseItemsPrice = 0;
        foreach ($items as $_item) {
            if ($_item->getParentItemId()) {
                continue;
            }
            if (in_array($_item->getId(), $discountedItems)) {
                continue;
            }
            if (!$program->validateItem($_item)) {
                continue;
            }
            // Changed By Adam 01/08/2014: don't calculate the items that belong to higher program's priority
            if (!$account->getUsingCoupon()
                && $this->_helper->checkItemInHigherPriorityProgram($account->getId(), $_item, $program->getPriority())
            ) {
                continue;
            }
            if ($_item->getHasChildren() && $_item->isChildrenCalculated()) {
                foreach ($_item->getChildren() as $child) {
                    if (!$discount_include_tax) {
                        $baseItemsPrice += $_item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                    } else {
                        $baseItemsPrice += $_item->getQty() * ($child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount());
                    }
                }
            } elseif ($_item->getProduct()) {
                if (!$discount_include_tax) {
                    $baseItemsPrice += $_item->getQty() * $_item->getBasePrice() - $_item->getBaseDiscountAmount();
                } else {
                    $baseItemsPrice += $_item->getQty() * $_item->getBasePriceInclTax() - $_item->getBaseDiscountAmount();
                }
            }
        }
        return $baseItemsPrice;
    }

    /**
     * @return mixed
     */
    protected
    function _priceIncludesTax()
    {
        return $this->_cookieHelper->getConfig(\Magento\Tax\Model\Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $this->getStore()->getId());
    }

    /**
     * @param $discountValue
     * @param $item
     * @param $discount_include_tax
     * @param $applyTaxAfterDiscount
     * @param $address
     * @param $store
     * @param $baseDiscount
     * @return float|int
     */
    protected
    function _getBaseDiscount($discountValue,
                              $item,
                              $discount_include_tax,
                              $applyTaxAfterDiscount,
                              $address,
                              $store,
                              $baseDiscount
    )
    {
        $itemBaseDiscount = 0;
        if ($discountValue > 100) {
            $discountValue = 100;
        }
        if ($discountValue < 0) {
            $discountValue = 0;
        }
        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
            foreach ($item->getChildren() as $child) {
                if (!$discount_include_tax) {
                    $price = $item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                } else {
                    $price = $item->getQty() * ($child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount());
                }
                $childBaseDiscount = $price * $discountValue / 100;
                $itemBaseDiscount += $childBaseDiscount;
                $child->setBaseAffiliateplusAmount($childBaseDiscount)
                    ->setAffiliateplusAmount($this->_abtractTemplate->convertPrice($childBaseDiscount));
                if ($applyTaxAfterDiscount) {
                    $baseTaxableAmount = $child->getBaseTaxableAmount();
                    $taxableAmount     = $child->getTaxableAmount();
                    $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
                    $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));
                    if ($this->_priceIncludesTax()) {
                        $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                        if ($rate > 0) {
                            $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $child->getBaseTaxableAmount(), $rate));
                            $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $child->getTaxableAmount(), $rate));
                        }
                    }
                }
            }
        } else {
            if (!$discount_include_tax) {
                $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
            } else {
                $price = $item->getQty() * $item->getBasePriceInclTax() - $item->getBaseDiscountAmount();
            }
            $itemBaseDiscount = $price * $discountValue / 100;
            $item->setBaseAffiliateplusAmount($itemBaseDiscount)
                ->setAffiliateplusAmount($this->_abtractTemplate->convertPrice($itemBaseDiscount));
            if ($applyTaxAfterDiscount) {
                $baseTaxableAmount = $item->getBaseTaxableAmount();
                $taxableAmount     = $item->getTaxableAmount();
                $item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $item->getBaseAffiliateplusAmount()));
                $item->setTaxableAmount(max(0, $taxableAmount - $item->getAffiliateplusAmount()));
                if ($this->_priceIncludesTax()) {
                    $rate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
                    if ($rate > 0) {
                        $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $item->getBaseTaxableAmount(), $rate));
                        $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $item->getTaxableAmount(), $rate));
                    }
                }
            }
        }
        $discountedItems[]    = $item->getId();
        $discountedProducts[] = $item->getProductId();
        $baseDiscount += $itemBaseDiscount;
        return $baseDiscount;
    }

    /**
     * @param $item
     * @param $discountValue
     * @param $discount_include_tax
     * @param $applyTaxAfterDiscount
     * @param $address
     * @param $store
     * @param $baseDiscount
     * @param $discountedItems
     * @param $discountedProducts
     * @return int
     */
    protected
    function _getBaseDiscount1(
        $item,
        $discountValue,
        $discount_include_tax,
        $applyTaxAfterDiscount,
        $address,
        $store,
        $baseDiscount,
        &$discountedItems,
        &$discountedProducts
    )
    {
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
                    ->setAffiliateplusAmount($this->_abtractTemplate->convertPrice($childBaseDiscount));
                if ($applyTaxAfterDiscount) {
                    $baseTaxableAmount = $child->getBaseTaxableAmount();
                    $taxableAmount     = $child->getTaxableAmount();
                    $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
                    $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));
                    if ($this->_priceIncludesTax()) {
                        $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                        if ($rate > 0) {
                            $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $child->getBaseTaxableAmount(), $rate));
                            $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $child->getTaxableAmount(), $rate));
                        }
                    }
                }
            }
        } else {
            $itemBaseDiscount = $item->getQty() * $discountValue;
            if (!$discount_include_tax) {
                $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
            } else {
                $price = $item->getQty() * $item->getBasePriceInclTax() - $item->getBaseDiscountAmount();
            }
            $itemBaseDiscount = ($itemBaseDiscount < $price) ? $itemBaseDiscount : $price;
            $item->setBaseAffiliateplusAmount($itemBaseDiscount)
                ->setAffiliateplusAmount($this->_abtractTemplate->convertPrice($itemBaseDiscount));
            if ($applyTaxAfterDiscount) {
                $baseTaxableAmount = $item->getBaseTaxableAmount();
                $taxableAmount     = $item->getTaxableAmount();
                $item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $item->getBaseAffiliateplusAmount()));
                $item->setTaxableAmount(max(0, $taxableAmount - $item->getAffiliateplusAmount()));
                if ($this->_priceIncludesTax()) {
                    $rate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
                    if ($rate > 0) {
                        $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $item->getBaseTaxableAmount(), $rate));
                        $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $item->getTaxableAmount(), $rate));
                    }
                }
            }
        }
        $discountedItems[]    = $item->getId();
        $discountedProducts[] = $item->getProductId();
        $baseDiscount += $itemBaseDiscount;
        return $baseDiscount;
    }

    /**
     * @param $discountValue
     * @param $baseItemsPrice
     * @param $items
     * @param $discountedItems
     * @param $discountedProducts
     * @param $account
     * @param $discount_include_tax
     * @param $applyTaxAfterDiscount
     * @param $address
     * @param $store
     * @param $item
     * @param $baseDiscount
     * @param $program
     * @return mixed
     */
    protected function _getBaseDiscount2(
        $discountValue,
        $baseItemsPrice,
        $items,
        &$discountedItems,
        &$discountedProducts,
        $account,
        $discount_include_tax,
        $applyTaxAfterDiscount,
        $address,
        $store,
        $item,
        $baseDiscount,
        $program
    )
    {
        $totalBaseDiscount = min($discountValue, $baseItemsPrice);
        foreach ($items as $_item) {
            if ($_item->getParentItemId()) {
                continue;
            }

            if (in_array($_item->getId(), $discountedItems)) {
                continue;
            }
            if (!$program->validateItem($_item)) {
                continue;
            }
            if (!$account->getUsingCoupon()
                && $this->_helper->checkItemInHigherPriorityProgram($account->getId(), $_item, $program->getPriority())
            ) {
                continue;
            }
            if ($_item->getHasChildren() && $_item->isChildrenCalculated()) {
                foreach ($_item->getChildren() as $child) {
                    if (!$discount_include_tax) {
                        $price = $_item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                    } else {
                        $price = $_item->getQty() * ($child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount());
                    }
                    $childBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                    $child->setBaseAffiliateplusAmount($childBaseDiscount)
                        ->setAffiliateplusAmount($this->_abtractTemplate->convertPrice($childBaseDiscount));
                    if ($applyTaxAfterDiscount) {
                        $baseTaxableAmount = $child->getBaseTaxableAmount();
                        $taxableAmount     = $child->getTaxableAmount();
                        $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
                        $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));
                        if ($this->_priceIncludesTax()) {
                            $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                            if ($rate > 0) {
                                $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $child->getBaseTaxableAmount(), $rate));
                                $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $child->getTaxableAmount(), $rate));
                            }
                        }
                    }
                }
            } elseif ($_item->getProduct()) {
                if (!$discount_include_tax) {
                    $price = $_item->getQty() * $_item->getBasePrice() - $_item->getBaseDiscountAmount();
                } else {
                    $price = $_item->getQty() * $_item->getBasePriceInclTax() - $_item->getBaseDiscountAmount();
                }
                $itemBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                $_item->setBaseAffiliateplusAmount($itemBaseDiscount)
                    ->setAffiliateplusAmount($this->_abtractTemplate->convertPrice($itemBaseDiscount));
                if ($applyTaxAfterDiscount) {
                    $baseTaxableAmount = $_item->getBaseTaxableAmount();
                    $taxableAmount     = $_item->getTaxableAmount();
                    $_item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $_item->getBaseAffiliateplusAmount()));
                    $_item->setTaxableAmount(max(0, $taxableAmount - $_item->getAffiliateplusAmount()));
                    if ($this->_priceIncludesTax()) {
                        $rate = $this->getItemRateOnQuote($address, $_item->getProduct(), $store);
                        if ($rate > 0) {
                            $_item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $_item->getBaseTaxableAmount(), $rate));
                            $_item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $_item->getTaxableAmount(), $rate));
                        }
                    }
                }
            }
            $discountedItems[]    = $_item->getId();
            $discountedProducts[] = $item->getProductId();
        }
        $baseDiscount += $totalBaseDiscount;
        return $baseDiscount;
    }
}