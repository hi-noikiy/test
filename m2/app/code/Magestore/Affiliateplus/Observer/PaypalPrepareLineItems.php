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
namespace Magestore\Affiliateplus\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magestore\Affiliateplus\Model\Transaction;

/**
 * Class ProductGetFinalPrice
 * @package Magestore\Affiliateplus\Observer
 */
class PaypalPrepareLineItems extends AbtractObserver implements ObserverInterface
{
    /**
     * Set Final Price to product in product list
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->isAffiliateModuleEnabled()){
            return $this;
        }

        $salesEntity = $observer->getSalesEntity();
        $additional = $observer->getAdditional();
        if ($salesEntity && $additional) {
            $totalDiscount = 0;
            if ($salesEntity->getBaseAffiliateplusDiscount()){
                $totalDiscount = $salesEntity->getBaseAffiliateplusDiscount();
            }else{
                foreach ($salesEntity->getAddressesCollection() as $address){
                    if ($address->getBaseAffiliateplusDiscount()){
                        $totalDiscount = $address->getBaseAffiliateplusDiscount();
                    }
                }
            }

            if ($totalDiscount) {
                $items = $additional->getItems();
                $items[] = new \Magento\Framework\DataObject(array(
                    'name' => __('Affiliate Discount'),
                    'qty' => 1,
                    'amount' => -(abs((float)$totalDiscount)),
                ));
                $additional->setItems($items);
            }

            /* Changed By Adam 16/04/2015 */
            $totalCredit = 0;
            if ($salesEntity->getBaseAffiliateCredit()){
                $totalCredit = $salesEntity->getBaseAffiliateCredit();
            }else{
                foreach ($salesEntity->getAddressesCollection() as $address){
                    if ($address->getBaseAffiliateCredit()){
                        $totalCredit = $address->getBaseAffiliateCredit();
                    }
                }
            }
            if ($totalCredit) {
                $items = $additional->getItems();
                $items[] = new \Magento\Framework\DataObject(array(
                    'name' => __('Affiliate Discount'),
                    'qty' => 1,
                    'amount' => -(abs((float)$totalCredit)),
                ));
                $additional->setItems($items);
            }
        }
    }
}