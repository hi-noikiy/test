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


class Mirasvit_Rewards_Helper_Purchase extends Mirasvit_MstCore_Helper_Help
{
    /**
     * @param int $quoteId
     * @return bool|Mirasvit_Rewards_Model_Purchase
     */
    public function getByQuote($quoteId)
    {
        $quote = false;
        if (is_object($quoteId)) {
            $quote = $quoteId;
            $quoteId = $quote->getId();
        }
        if (!$quoteId) {
            return false;
        }
        $collection = Mage::getModel('rewards/purchase')->getCollection()
                        ->addFieldToFilter('quote_id', $quoteId);
        if ($collection->count()) {
            /** @var Mirasvit_Rewards_Model_Purchase $purchase */
            $purchase = $collection->getFirstItem();
            if ($quote) {
                $purchase->setQuote($quote);
            }
        } else {
            $purchase = Mage::getModel('rewards/purchase')->setQuoteId($quoteId);
            if ($quote) {
                $purchase->setQuote($quote);
            }
            $purchase->save();
        }
        return $purchase;
    }


    /**
     * @param Mage_Sales_Model_Order $order
     * @return bool|Mirasvit_Rewards_Model_Purchase
     */
    public function getByOrder($order)
    {
        $collection = Mage::getModel('rewards/purchase')->getCollection()
            ->addFieldToFilter('order_id', $order->getId());
        if ($collection->count()) {
            $purchase = $collection->getFirstItem();
            //some 3rd party extensions may replace quote for order
            if ($purchase->getQuoteId() != $order->getQuoteId()) {
                $collection = Mage::getModel('rewards/purchase')->getCollection()
                    ->addFieldToFilter('quote_id', $order->getQuoteId());
                foreach ($collection as $item) {
                    $item->delete();
                }
                $purchase->setQuoteId($order->getQuoteId())
                    ->save();
            }
        } else {
            $purchase = $this->getByQuote($order->getQuoteId());
            if ($purchase && !$purchase->getOrderId()) {
                $purchase->setOrderId($order->getId())->save();
            }
        }
        return $purchase;
    }

    /**
     * @return bool|Mirasvit_Rewards_Model_Purchase
     */
    public function getPurchase()
    {
        $quote = Mage::getModel('checkout/cart')->getQuote();
        return $this->getByQuote($quote);
    }

}