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
namespace Magestore\Affiliateplus\Model\Plugin\Quote;

class DiscountToOrderItem
{
    /**
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param array $additional
     * @return Item
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {
        /** @var $orderItem Item */
        $orderItem = $proceed($item, $additional);
        if ($item->getAffiliateplusAmount()) {
            $orderItem->setAffiliateplusAmount($item->getAffiliateplusAmount());
            $orderItem->setBaseAffiliateplusAmount($item->getBaseAffiliateplusAmount());
        }
        if ($item->getAffiliateplusCredit()) {
            $orderItem->setAffiliateplusCredit($item->getAffiliateplusCredit());
            $orderItem->setBaseAffiliateplusCredit($item->getBaseAffiliateplusCredit());
        }

        return $orderItem;
    }
}