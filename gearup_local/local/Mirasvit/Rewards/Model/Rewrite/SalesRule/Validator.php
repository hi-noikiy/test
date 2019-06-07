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



$isVoronoyExtraFee = Mage::helper('mstcore')->isModuleInstalled('Voronoy_ExtraFee') &&
    @class_exists('Voronoy_ExtraFee_Model_SalesRule_Discount_Validator');
if ($isVoronoyExtraFee) {
    abstract class Mirasvit_Rewards_Model_Rewrite_SalesRule_Validator_Abstract
        extends Voronoy_ExtraFee_Model_SalesRule_Discount_Validator
    {}
} else {
    abstract class Mirasvit_Rewards_Model_Rewrite_SalesRule_Validator_Abstract extends Mage_SalesRule_Model_Validator
    {}
}

class Mirasvit_Rewards_Model_Rewrite_SalesRule_Validator extends Mirasvit_Rewards_Model_Rewrite_SalesRule_Validator_Abstract
{
    /**
     * @return Mirasvit_Rewards_Model_Config
     */
    protected function getConfig() {
        return Mage::getSingleton('rewards/config');
    }

    public function process(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        parent::process($item);

        //we don't apply rewards discount during grand total calculation
        if (!$this->getConfig()->getCalculateTotalFlag()) {
            return $this;
        }
        $quote      = $item->getQuote();
        $address    = $this->_getAddress($item);

        $config = Mage::getSingleton('rewards/config');
        $items = array_keys($quote->getTotals());

        if ($config->getGeneralIsAllowRewardsAndCoupons() != Mirasvit_Rewards_Model_Config::COUPONS_ENABLED
            && $quote->getCouponCode()
        ) {
            return $this;
        }

        if (Mage::helper('rewards')->isMultiship($address)) {
            return $this;
        }
        if (!$quote->getId()) {
            return $this;
        }
        $purchase = Mage::helper('rewards/purchase')->getByQuote($quote);

        if (!$purchase->getSpendAmount()) {
            return $this;
        }
        $spendAmount = $purchase->getSpendAmount();
        if ($spendAmount == 0) {
            return $this;
        }
//        echo 'discount'.$spendAmount.'<br>';
        //store can have base currency (e.g. USD) and current currentcy (e.g. EUR).
        //prices and discounts will be different
        $itemTotalPrice  = $item->getData('row_total_incl_tax'); //price with TAX
        if ($itemTotalPrice == 0) { //in some cases, when we add a new configurable item to the cart, it has not updated the price yet.
            return $this;
        }
        $total = $address->getSubtotalInclTax(); //price with TAX
        if ($total == 0) { //in some cases (maybe conflict) getSubtotalInclTax is empty
            $total = $address->getSubtotal(); 
        }
        //now we need to check: should we make order grand total = 0.01 or not.
        if (!$this->getConfig()->getGeneralIsAllowZeroOrders()) {
            $priceIncludesTax = Mage::helper('tax')->priceIncludesTax($quote->getStore());
            $addr = $quote->getShippingAddress();
            if ($priceIncludesTax) {
                $grandTotal = $addr->getBaseSubtotalInclTax() + $addr->getBaseShippingInclTax();
            } else {
                $grandTotal = $addr->getBaseSubtotal() + $addr->getBaseShippingAmount();
            }
            if ($grandTotal == $spendAmount) {
                $spendAmount -= 0.01;
            }
        }

        $discount = $itemTotalPrice/$total * $spendAmount;

        $baseItemTotalPrice  = $item->getData('base_row_total_incl_tax'); //price with TAX
        $baseTotal = $address->getBaseSubtotalInclTax(); //price with TAX
        $baseSpendAmount = $spendAmount * $baseItemTotalPrice / $itemTotalPrice ;
        $baseDiscount = $baseItemTotalPrice/$baseTotal * $baseSpendAmount;

        $item->setDiscountAmount($discount + $item->getDiscountAmount());
        $item->setBaseDiscountAmount($baseDiscount + $item->getBaseDiscountAmount());

        return $this;
    }

    public function prepareDescription($address, $separator = ', ')
    {
        parent::prepareDescription($address, $separator);
        $this->setDiscountDescription($address);
        return $this;
    }

    protected function setDiscountDescription($address)
    {
        $purchase = Mage::helper('rewards/purchase')->getByQuote($address->getQuote());
        if ($purchase && $address->getAddressType() == 'shipping' && $purchase->getSpendAmount()) {
            $description = Mage::helper('rewards')->formatPoints($purchase->getSpendPoints());
            if ($address->getDiscountDescription()) {
                $description = $address->getDiscountDescription().', '.$description;
            }
            $address->setDiscountDescription($description);
        }
    }
}
