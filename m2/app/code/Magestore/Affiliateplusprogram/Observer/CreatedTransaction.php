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

class CreatedTransaction extends AbtractObserver implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->isPluginEnabled()) {
            return;
        }
        $transaction = $observer->getEvent()->getTransaction();
        $order = $observer->getEvent()->getOrder();
        $extraContent = $transaction->getExtraContent();
        $originalCommission = $transaction->getOriginalCommission();

        if ($extraContent && count($extraContent)) {
            $transactionModel = $this->_transactionFactory->create()
                ->setTransactionId($transaction->getId())
                ->setOrderId($transaction->getOrderId())
                ->setOrderNumber($transaction->getOrderNumber())
                ->setAccountId($transaction->getAccountId())
                ->setAccountName($transaction->getAccountName());

            $program = $this->_programFactory->create()
                ->setStoreId($this->_storeManager->getStore()->getId());
            foreach ($extraContent as $programId => $programData) {
                $transactionModel->addData($programData);
                $transactionModel->setOrderItemIds(implode(',', $programData['order_item_ids']))
                    ->setOrderItemNames(implode(',', $programData['order_item_names']))
                    ->setProgramId($programId)
                    ->setCommission($programData['commission'])
                    ->setId(null)
                    ->save();
                $program->load($programId);
                $check = true;
                $program->setTotalSalesAmount($program->getTotalSalesAmount() + $transactionModel->getTotalAmount())
                    ->orgSave();
            }

            if ($transaction->getDefaultCommission())
                    $transactionModel->setOrderItemIds(implode(',', $transaction->getDefaultItemIds()))
                        ->setOrderItemNames(implode(',', $transaction->getDefaultItemNames()))
//                        ->setProgramId(0)
                        ->setProgramName(__('Affiliate Program'))
                        ->setCommission($transaction->getDefaultCommission())
                        ->setTotalAmount($transaction->getDefaultAmount())
                        ->setId(null)
                        ->save();
        }
        return $this;
    }
}