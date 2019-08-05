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

namespace Magestore\Affiliateplusprogram\Observer\Frontend;

use Magento\Framework\Event\ObserverInterface;
use Magestore\Affiliateplusprogram\Observer\AbtractObserver;

class AffiliateplusCancelTransactionMltipleprogram extends AbtractObserver implements ObserverInterface
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
        $transaction        = $observer->getTransaction();
        $transactionStatus  = $observer->getStatus();
        $programTransaction = $this->_transactionFactory->create()
            ->load($transaction->getId(), "transaction_id");
        if ($programTransaction && $programTransaction->getId()) {
            try {
                $programTransaction->setCommission(0)
                    ->save();
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }
}