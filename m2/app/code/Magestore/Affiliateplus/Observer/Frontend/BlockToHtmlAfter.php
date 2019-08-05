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
use Magento\Framework\Event\Observer;
use Magestore\Affiliateplus\Observer\AbtractObserver;

/**
 * Class ProductGetFinalPrice
 * @package Magestore\Affiliateplus\Observer
 */
class BlockToHtmlAfter extends AbtractObserver implements ObserverInterface
{
    /**
     * Add store credit form to checkout cart page
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        if(!$this->_helper->isAffiliateModuleEnabled()){
            return $this;
        }
        $helper = $this->_helperAccount;
        if ($helper->accountNotLogin() || $helper->disableStoreCredit() || !$helper->isEnoughBalance()){
            return $this;
        }

        if ($observer['element_name']=='checkout.cart.coupon') {
            $data = $observer['transport']->getData('output');
            $creditFormHtml = $observer['layout']->createBlock('Magestore\Affiliateplus\Block\Credit\Cart')
                ->toHtml();
            $observer['transport']->setData('output', $data.$creditFormHtml);
        };
    }
}