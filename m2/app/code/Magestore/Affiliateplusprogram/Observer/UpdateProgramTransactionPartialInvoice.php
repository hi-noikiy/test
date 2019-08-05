<?php
/**
 * Magestore
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
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Affiliateplusprogram\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateProgramTransactionPartialInvoice extends AbtractObserver implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->isPluginEnabled()) {
            return;
        }
        $transaction = $observer->getTransaction();
        $creditMemo = $observer->getOrder();

        $programTransactions = $this->_programTransactionCollectionFactory->create()
            ->addFieldToFilter('transaction_id', $transaction->getId());

        try {
            foreach ($programTransactions as $programTransaction) {
                $commission = 0;
                $orderItemIds = explode(",", $programTransaction->getOrderItemIds());

                foreach ($creditMemo->getAllItems() as $orderItem) {
//                    $orderItem = $creditMemoItem->getOrderItem();

                    if (in_array($orderItem->getProductId(), $orderItemIds)) {

                        $affiliateplusCommissionItem = explode(",", $orderItem->getAffiliateplusCommissionItem());
                        $totalComs = array_sum($affiliateplusCommissionItem);

                        $commission += $totalComs * $orderItem->getQty() / $orderItem->getQtyOrdered();
                    }
                }
                if ($commission) {
                    $programTransaction->setCommission($programTransaction->getCommission() - $commission)
                        ->save();
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}