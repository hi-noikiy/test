<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Model_Total_Quote_StoreCredit extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    public function __construct()
    {
        $this->setCode('amstcred');
    }


    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $quote = $address->getQuote();
        if (!Mage::helper('amstcred')->isModuleActive() || !Mage::helper('amstcred')->isAllowedStoreCredit($quote)) {
            $quote->setBaseAmstcredAmountUsed(0);
            $quote->setAmstcredAmountUsed(0);
            $quote->setAmstcredUseCustomerBalance(false);
            $address->setBaseAmstcredAmount(0);
            $address->setAmstcredAmount(0);
            return $this;
        }

        if (!$quote->getAmstcredBalanceCollected()) {
            $quote->setBaseAmstcredAmountUsed(0);
            $quote->setAmstcredAmountUsed(0);

            $quote->setAmstcredBalanceCollected(true);
        }

        $baseTotalUsed = $totalUsed = $baseUsed = $used = 0;

        $baseBalance = $balance = 0;
        if ($quote->getCustomer()->getId()) {
            if ($quote->getAmstcredUseCustomerBalance()) {
                $store = Mage::app()->getStore($quote->getStoreId());
                $baseBalance = Mage::getModel('amstcred/balance')
                    ->setCustomer($quote->getCustomer())
                    ->setCustomerId($quote->getCustomer()->getId())
                    ->setWebsiteId($store->getWebsiteId())
                    ->loadByCustomer()
                    ->getAmount();
                $balance = $quote->getStore()->convertPrice($baseBalance);
            }
        }

        $baseAmountLeft = $baseBalance - $quote->getBaseAmstcredAmountUsed();
        $amountLeft = $balance - $quote->getAmstcredAmountUsed();


        if ($baseAmountLeft > 0 && $baseAmountLeft >= $address->getBaseGrandTotal()) {
            $baseUsed = $address->getBaseGrandTotal();
            $used = $address->getGrandTotal();

            $address->setBaseGrandTotal(0);
            $address->setGrandTotal(0);
        } else {
            $baseUsed = $baseAmountLeft;
            $used = $amountLeft;

            $address->setBaseGrandTotal($address->getBaseGrandTotal() - $baseAmountLeft);
            $address->setGrandTotal($address->getGrandTotal() - $amountLeft);
        }

        $baseTotalUsed = $quote->getBaseAmstcredAmountUsed() + $baseUsed;
        $totalUsed = $quote->getAmstcredAmountUsed() + $used;

        $quote->setBaseAmstcredAmountUsed($baseTotalUsed);
        $quote->setAmstcredAmountUsed($totalUsed);

        $address->setBaseAmstcredAmount($baseUsed);
        $address->setAmstcredAmount($used);

        return $this;

    }


    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if (!Mage::helper('amstcred')->isModuleActive()) {
            return $this;
        }
        if ($address->getAmstcredAmount()) {
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => Mage::helper('amstcred')->__('Store Credit'),
                'value' => -$address->getAmstcredAmount(),
            ));
        }
        return $this;
    }
}
