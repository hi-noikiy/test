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
namespace Magestore\Affiliateplus\Helper\Payment;

use Magento\TestFramework\Event\Magento;

class Tax extends \Magestore\Affiliateplus\Helper\HelperAbstract
{
    /**
     * @var null
     */
    protected $_calculator = null;
    /**
     * get tax calculation object
     *
     * @return Magento\Tax\Model\Calculation
     */
    public function getCalculator() {
        if ($this->_calculator === null) {
            $this->_calculator = $this->_objectManager->create('Magento\Tax\Model\Calculation');
        }
        return $this->_calculator;
    }

    /**
     * Check enable tax calculation or not
     *
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function isTaxEnable($store = null) {
        return $this->getConfig('affiliateplus/payment/tax_class', $store);
    }

    /**
     * get Tax rate (percent) for customer
     *
     * @param Magestore_Affiliateplus_Model_Account $account
     * @param Mage_Core_Model_Store $store
     * @return float
     */
    public function getTaxRate($account = null, $store = null) {
        $store = $this->_storeManager->getStore($store);

        $taxClassId = $this->getConfig('affiliateplus/payment/tax_class', $store);
        if (!$taxClassId) return 0;

        $calculator = $this->getCalculator();
        $customer = $calculator->getCustomer();
        if (!$customer) {
            if($account && $account->getCustomerId()){
                $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($account->getCustomerId());
                $calculator->setCustomer($customer);
            }
        }
        if ($customer){
            $request = $calculator->getRateRequest(null, null, null, $store,$customer->getId());
        }else{
            $request = $calculator->getRateRequest(null, null, null, $store);
        }
        $percent = $calculator->getRate($request->setProductClassId($taxClassId));
        return (float)$percent;
    }

    /**
     * calculate tax amount for account
     *
     * @param float $price
     * @param Magestore_Affiliateplus_Model_Account $account
     * @param Mage_Core_Model_Store $store
     * @return float
     */
    public function getPriceTaxAmount($price, $account, $store = null) {
        $store = $this->_storeManager->getStore($store);

        $rate = $this->getTaxRate($account, $store);
        $taxAmount = $price * $rate / 100;
        return $taxAmount;
//        return $store->roundPrice($taxAmount);
    }

    /**
     * Calculate tax amount
     *
     * @param float $amount
     * @param float $fee
     * @param Magestore_Affiliateplus_Model_Account $account
     * @param Mage_Core_Model_Store $store
     * @return float
     */
    public function getTaxAmount($amount, $fee, $account, $store = null) {
        return $this->getPriceTaxAmount($amount, $account, $store);
    }
}