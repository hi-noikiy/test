<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rewards
 * @version   1.1.35
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rewards_Helper_Balance_Spend
{
    const MIN_START = 999999999;

    /**
     * @var array
     */
    private $validatedItems = array();
    /**
     * @var array
     */
    private $validatedItemsMin = array();

    /**
     * @return Mirasvit_Rewards_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('rewards/config');
    }

    /**
     * @param Mage_Sales_Model_Quote               $quote
     * @param Mirasvit_Rewards_Model_Spending_Rule $rule
     *
     * @return float
     */
    protected function getLimitedSubtotal($quote, $rule)
    {
        $subtotal = 0;
        $priceIncludesTax = Mage::helper('tax')->priceIncludesTax($quote->getStore());
        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            $parentItem = null;
            if ($item->getParentItemId()) {
                $parentItem = Mage::getModel('sales/quote_item')->load($item->getParentItemId());
                $parentProduct = Mage::getModel('catalog/product')->load($parentItem->getProductId());
                if ($parentProduct->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                    continue;
                }
            }
            if ($rule->getActions()->validate($item)) {
                if ($parentItem) {
                    $quantity = $parentItem->getQty() * $item->getQty();
                } else {
                    $quantity = $item->getQty();
                }
                if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                    if ($priceIncludesTax) {
                        $itemPrice = $item->getPriceInclTax();
                    } else {
                        $itemPrice = $item->getPrice();
                    }
                } else {
                    if ($priceIncludesTax) {
                        $itemPrice = $item->getBasePriceInclTax();
                    } else {
                        $itemPrice = $item->getBasePrice();
                    }
                }
                $itemSubtotal = $itemPrice * $quantity - $item->getBaseDiscountAmount();
                $this->calculateMaxPointsForItem($rule, $item, $itemSubtotal);
                $this->calculateMinPointsForItem($rule, $item, $itemSubtotal);

                $subtotal += $itemSubtotal;
            }
        }

        return $subtotal;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return Mirasvit_Rewards_Model_Resource_Spending_Rule_Collection
     */
    protected function getRules($quote)
    {
        $customerGroupId = $quote->getCustomerGroupId();
        $websiteId = $quote->getStore()->getWebsiteId();
        $rules = Mage::getModel('rewards/spending_rule')->getCollection()
                    ->addWebsiteFilter($websiteId)
                    ->addCustomerGroupFilter($customerGroupId)
                    ->addCurrentFilter()
                    ->setOrder('sort_order')
                    ;

        return $rules;
    }


    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return Varien_Object
     */
    public function getCartRange($quote)
    {
        $rules = $this->getRules($quote);

        $shipping = 0;
        $maxPoints = 0;
        $minPoints = self::MIN_START;
        /** @var Mirasvit_Rewards_Model_Spending_Rule $rule */
        $maxSpendLimit = 0;
        foreach ($rules as $rule) {
            $rule->afterLoad();
            if ($quote->getItemVirtualQty() > 0) {
                $address = $quote->getBillingAddress();
            } else {
                $address = $quote->getShippingAddress();
            }
            if ($rule->validate($address)) {
                $subtotal = $this->getLimitedSubtotal($quote, $rule);
                $shipping = $this->calculateShippingPoints($quote, $rule);
                if ($rule->getSpendMaxAmount($subtotal) > 0) {
                    //$maxSpendLimit += $rule->getSpendMaxAmount($subtotal) / $rule->getMonetaryStep() *
                    //    $rule->getSpendPoints();
                    $maxSpendLimit += $rule->getSpendMaxAmount($subtotal);
                }
                if ($rule->getIsStopProcessing()) {
                    break;
                }
            }
        }
        if ($this->validatedItems) {
            $maxPoints = array_sum($this->validatedItems['points']) + $shipping;
            if ($maxSpendLimit) {
                $maxPoints = min($maxPoints, $maxSpendLimit);
            }
        }
        if ($this->validatedItemsMin) {
            $minPoints = min($this->validatedItemsMin['points']);
        }
        if ($minPoints > $maxPoints) {
            $minPoints = $maxPoints = 0;
        }

        return new Varien_Object(array('min_points' => $minPoints, 'max_points' => $maxPoints));
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param int                    $pointsNumber
     * @return Varien_Object
     */
    public function getCartPoints($quote, $pointsNumber)
    {
        $shipping = 0;
        $spendPoints = 0;
        $totalAmount = 0;
        $rules = $this->getRules($quote);
        /** @var Mirasvit_Rewards_Model_Spending_Rule $rule */
        foreach ($rules as $rule) {
            $rule->afterLoad();
            if ($quote->getItemVirtualQty() > 0) {
                $address = $quote->getBillingAddress();
            } else {
                $address = $quote->getShippingAddress();
            }
            if ($rule->validate($address)) {
                $this->getLimitedSubtotal($quote, $rule);
                $shipping = $this->calculateShippingPoints($quote, $rule);
                if ($rule->getIsStopProcessing()) {
                    break;
                }
            }
        }
        if ($this->validatedItems) {
            $maxSpendPoints = array_sum($this->validatedItems['points']) + $shipping;
            $minSpendPoints = min($this->validatedItemsMin['points']);

            if ($pointsNumber > $maxSpendPoints) {
                $pointsNumber = $maxSpendPoints;
            }
            if ($pointsNumber < $minSpendPoints) {
                $pointsNumber = 0;
            }
            foreach ($this->validatedItems['priority'] as $itemId => $order) {
                $points = $this->validatedItems['points'][$itemId];
                $stepPoints = $points;
                if (($spendPoints + $points) > $pointsNumber) {
                    $stepPoints = $pointsNumber - $spendPoints;
                }
                $totalAmount += $stepPoints/$this->validatedItems['spend_points'][$itemId] *
                    $this->validatedItems['step'][$itemId];
                $spendPoints += $stepPoints;
                if ($spendPoints == $pointsNumber) {
                    break;
                }
            }

            if ($shipping && $spendPoints < $pointsNumber && ($spendPoints + $shipping) >= $pointsNumber) {
                $percent = $pointsNumber/($spendPoints + $shipping);
                $spendPoints = ($spendPoints + $shipping) * $percent;
                $totalAmount = ($totalAmount + $quote->getShippingAddress()->getBaseShippingInclTax()) * $percent;
            }
        }

        // Make correction on currency rate
        if ($quote->getQuoteCurrencyCode() != Mage::app()->getStore()->getBaseCurrencyCode()) {
            $currency = Mage::getModel('directory/currency')->load($quote->getQuoteCurrencyCode());
            $rate = Mage::app()->getStore()->getBaseCurrency()->getRate($currency);
            $totalAmount = $totalAmount * $rate;
        }

        return new Varien_Object(array('points' => $spendPoints, 'amount' => $totalAmount));
    }

    /**
     * @param Mage_Sales_Model_Quote               $quote
     * @param Mirasvit_Rewards_Model_Spending_Rule $rule
     * @return int
     */
    protected function calculateShippingPoints($quote, $rule)
    {
        $conditions = $rule->getConditions()->asArray();

        $points = 0;
        if (empty($conditions['condition']) && $this->getConfig()->getGeneralIsSpendShipping()) {
            $shipping = $quote->getShippingAddress()->getBaseShippingInclTax();

            $points = (int) ($shipping / $rule->getMonetaryStep() * $rule->getSpendPoints());
        }

        return $points;
    }

    /**
     * @param Mirasvit_Rewards_Model_Spending_Rule $rule
     * @param Mage_Sales_Model_Quote_Item          $item
     * @param int                                  $itemSubtotal
     * @return void
     */
    protected function calculateMaxPointsForItem($rule, $item, $itemSubtotal)
    {
        $stepOne = $itemSubtotal / $rule->getMonetaryStep();
        if ($max = $rule->getSpendMaxAmount($itemSubtotal)) {
            $stepsMax = (int) ($max / $rule->getMonetaryStep());
            $stepOne   = min($stepOne, $stepsMax);
        }
        $maxPointsForThis = $stepOne * $rule->getSpendPoints();
        if ($rule->getSpendMaxPointsNumber()) {
            $maxPointsForThis = min($maxPointsForThis, $rule->getSpendMaxPointsNumber());
        }
        if (
            !isset($this->validatedItems['points'][$item->getId()]) ||
            $this->validatedItems['points'][$item->getId()] < $maxPointsForThis
        ) {
            $this->validatedItems['points'][$item->getId()]       = (int)$maxPointsForThis;
            $this->validatedItems['spend_points'][$item->getId()] = $rule->getSpendPoints();
            $this->validatedItems['step'][$item->getId()]         = $rule->getMonetaryStep();
            $this->validatedItems['priority'][$item->getId()]     = $rule->getSortOrder();
        }

        asort($this->validatedItems['priority']);
    }

    /**
     * @param Mirasvit_Rewards_Model_Spending_Rule $rule
     * @param Mage_Sales_Model_Quote_Item          $item
     * @param int                                  $itemSubtotal
     * @return void
     */
    protected function calculateMinPointsForItem($rule, $item, $itemSubtotal)
    {
        $localMinPoints = self::MIN_START;
        if ($rule->getSpendMinPointsNumber()) {
            $localMinPoints = min($localMinPoints, $rule->getSpendMinPointsNumber());
        }

        if ($min = $rule->getSpendMinAmount($itemSubtotal)) {
            $stepsMin       = (int) ($min / $rule->getMonetaryStep());
            $localMinPoints = min($stepsMin, $localMinPoints);
        }
        if ($localMinPoints == self::MIN_START) {
            $localMinPoints = 0;
        }
        $minPoints = max($localMinPoints, $rule->getSpendPoints());
        if ($minPoints == self::MIN_START) {
            $minPoints = 0;
        }

        if (
            !isset($this->validatedItemsMin['points'][$item->getId()]) ||
            $this->validatedItemsMin['points'][$item->getId()] > $minPoints
        ) {
            $this->validatedItemsMin['points'][$item->getId()]       = $minPoints;
            $this->validatedItemsMin['spend_points'][$item->getId()] = $rule->getSpendPoints();
            $this->validatedItemsMin['step'][$item->getId()]         = $rule->getMonetaryStep();
            $this->validatedItemsMin['priority'][$item->getId()]     = $rule->getSortOrder();
        }

        asort($this->validatedItemsMin['priority']);
    }
}
