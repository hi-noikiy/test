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



class Mirasvit_Rewards_Helper_Balance_Order
{
    /**
     * Returns store ID, bounded with current order. If order is not defined, returns current store.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Core_Model_Store
     */
    public function getStoreByOrder($order)
    {
        return ($order) ?
            Mage::getModel('core/store')->load($order->getStoreId()) :
            Mage::helper('rewards')->getCurrentStore();
    }

    /**
     * Calculates and adds points, based on order items and subtotal.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return int
     */
    public function earnOrderPoints($order)
    {
        if ($this->getEarnedPointsTransaction($order)) {
            return false;
        }

        $collection = Mage::getModel('rewards/purchase')->getCollection()
            ->addFieldToFilter('quote_id', $order->getQuoteId());
        if ($collection->count()) { //we have new version of data in DB
            $purchase = Mage::helper('rewards/purchase')->getByOrder($order);
            $totalPoints = $purchase->getEarnPoints();
        } else { //we need this for compability with older versions.
            $collection = Mage::getModel('sales/quote')->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('entity_id', $order->getQuoteId());
            $quote = $collection->getFirstItem();
            $totalPoints = Mage::helper('rewards/balance_earn')->getPointsEarned($quote);
        }
        if ($totalPoints) {
            Mage::helper('rewards')->setCurrentStore($this->getStoreByOrder($order));
            Mage::helper('rewards/balance')->changePointsBalance($order->getCustomerId(), $totalPoints,
                Mage::helper('rewards')->____('Earned %s for the order #%s.',
                Mage::helper('rewards')->formatPoints($totalPoints), 'o|' . $order->getIncrementId()),
                'order_earn-'.$order->getId(), true);

            return $totalPoints;
        }
    }

    /**
     * Cancels earned points.
     *
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Order_Creditmemo $creditMemo
     *
     * @return bool
     */
    public function cancelEarnedPoints($order, $creditMemo)
    {
        $earnedTransaction = $this->getEarnedPointsTransaction($order);
        if (!$earnedTransaction || $this->getExpiredPointsTransaction($order)) {
            return false;
        }
        $proportion = 1;
        if ($creditMemo) {
            $proportion = $creditMemo->getSubtotal() / $order->getSubtotal();
            if ($proportion > 1) {
                $proportion = 1;
            }
        }
        $totalPoints = round($earnedTransaction->getAmount() * $proportion);
        $code = $earnedTransaction->getCode();
        Mage::helper('rewards')->setCurrentStore($this->getStoreByOrder($order));
        Mage::helper('rewards/balance')->changePointsBalance($earnedTransaction->getCustomerId(), -$totalPoints,
            Mage::helper('rewards')->____('Cancel earned %s for the order #%s.',
            Mage::helper('rewards')->formatPoints($totalPoints), 'o|' . $order->getIncrementId()),
            substr($code, 0, strpos($code, '-')).'_cancel-'.$order->getId(), false);
    

        $code = substr($code, 0, strpos($code, '-')).'_cancel-'.$order->getId();
        $items = array();
        foreach ($creditMemo->getAllItems() as $item) {
            if ($item->getQty() > 0) {
                $orderItem = $item->getOrderItem();
                $items[] = 'item-'.$orderItem->getId().'_'.$orderItem->getQtyRefunded();
            }
        }
        $code .= '__'.implode('__', $items);
        Mage::helper('rewards/balance')->changePointsBalance(
            $earnedTransaction->getCustomerId(),
            -$totalPoints,
            Mage::helper('rewards')->____(
                'Cancel earned %s for the order #%s.',
                Mage::helper('rewards')->formatPoints($totalPoints),
                'o|' . $order->getIncrementId()
            ),
            $code,
            false
        );
    }
    /**
     * Cancels earned points without credit memo.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return bool
     */
    public function cancelEarnedPointsWithNoCreditMemo($order)
    {
        if ((!$earnedTransaction = $this->getEarnedPointsTransaction($order)) ||
            $this->getExpiredPointsTransaction($order)) {
            return false;
        }

        $proportion = 1;

        $totalPoints = round($earnedTransaction->getAmount() * $proportion);
        $code = $earnedTransaction->getCode();
        Mage::helper('rewards')->setCurrentStore($this->getStoreByOrder($order));
        Mage::helper('rewards/balance')->changePointsBalance($earnedTransaction->getCustomerId(), -$totalPoints,
            Mage::helper('rewards')->____('Cancel earned %s for the order #%s.',
                Mage::helper('rewards')->formatPoints($totalPoints), 'o|' . $order->getIncrementId()),
            substr($code, 0, strpos($code, '-')).'_cancel-'.$order->getId(), false);
    }
    
    /**
     * Decreases the number of points on the customer account.
     *
     * @param Mage_Sales_Model_Order $order
     * @return int
     */
    public function spendOrderPoints($order)
    {
        if (!$purchase = Mage::helper('rewards/purchase')->getByOrder($order)) {
            return 0;
        }
        if ($totalPoints = $purchase->getSpendPoints()) {
            Mage::helper('rewards')->setCurrentStore($this->getStoreByOrder($order));
            Mage::helper('rewards/balance')->changePointsBalance($order->getCustomerId(), -$totalPoints,
                Mage::helper('rewards')->____('Spent %s for the order #%s.',
                Mage::helper('rewards')->formatPoints($totalPoints), 'o|' . $order->getIncrementId()),
                'order_spend-'.$order->getId(), false);

            return $totalPoints;
        }
    }

    /**
     * @param Mage_Sales_Model_Order                 $order
     * @param Mage_Sales_Model_Order_Creditmemo|bool $creditMemo
     * @return void
     */
    public function restoreSpendPoints($order, $creditMemo = false)
    {
        if (!$spendTransaction = $this->getSpendPointsTransaction($order)) {
            return;
        }
        if ($creditMemo) { //if we create a credit memo
            $proportion = $creditMemo->getSubtotal() / $order->getSubtotal();
            if ($proportion > 1) {
                $proportion = 1;
            }
            $totalPoints = round($spendTransaction->getAmount() * $proportion);
        } else { //if we cancel order via backend
            $totalPoints = $spendTransaction->getAmount();
        }
        Mage::helper('rewards')->setCurrentStore($this->getStoreByOrder($order));
        $totalPoints = $spendTransaction->getAmount();
        Mage::helper('rewards/balance')->changePointsBalance($order->getCustomerId(), -$totalPoints,
            Mage::helper('rewards')->____('Restore spent %s for the order #%s.',
            Mage::helper('rewards')->formatPoints($totalPoints), 'o|' . $order->getIncrementId()),
            'order_spend_restore-'.$order->getId(), false);
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return Mirasvit_Rewards_Model_Transaction
     */
    protected function getExpiredPointsTransaction($order)
    {
        if ($earnTransaction = $this->getEarnedPointsTransaction($order)) {
            $expired = Mage::getModel('rewards/transaction')->getCollection()
                ->addFieldToFilter('code', "expired-{$earnTransaction->getId()}");
            if ($expired->count()) {
                return $expired->getFirstItem();
            }
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return Mirasvit_Rewards_Model_Transaction
     */
    protected function getEarnedPointsTransaction($order)
    {
        $collection = Mage::getModel('rewards/transaction')->getCollection()
            ->addFieldToFilter('code', array("order_earn-{$order->getId()}",
                "referred_customer_order-{$order->getId()}"))
        ;
        if ($collection->count()) {
            return $collection->getFirstItem();
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return Mirasvit_Rewards_Model_Transaction
     */
    protected function getSpendPointsTransaction($order)
    {
        $collection = Mage::getModel('rewards/transaction')->getCollection()
            ->addFieldToFilter('code', "order_spend-{$order->getId()}")
        ;
        if ($collection->count()) {
            return $collection->getFirstItem();
        }
    }
}
