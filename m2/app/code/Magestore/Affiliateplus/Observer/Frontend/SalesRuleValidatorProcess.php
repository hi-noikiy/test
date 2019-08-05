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
namespace Magestore\Affiliateplus\Observer\Frontend;
use Magento\Framework\Event\ObserverInterface;
use Magestore\Affiliateplus\Observer\AbtractObserver;

/**
 * Class ProductGetFinalPrice
 * @package Magestore\Affiliateplus\Observer
 */
class SalesRuleValidatorProcess extends AbtractObserver implements ObserverInterface
{
    /**
     * Reset Salerule Discount
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->_helper->isAffiliateModuleEnabled())
            return $this;

        if ($this->_helperConfig->getDiscountConfig('allow_discount') != 'affiliate') {
            return $this;
        }

        $affiliateInfo = $this->_helperCookie->getAffiliateInfo();
        $account = '';
        foreach ($affiliateInfo as $info){
            if ($info['account']) {
                $account = $info['account'];
                break;
            }
        }

        if (!$account) {
            return $this;
        }
        $result = $observer['result'];
        $result->setAmount(0)
            ->setBaseAmount(0);
        $rule = $observer['rule'];
        $rule->setRuleId('')->setStopRulesProcessing(true);
    }
}