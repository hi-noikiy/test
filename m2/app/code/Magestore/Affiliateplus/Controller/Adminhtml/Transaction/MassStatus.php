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
namespace Magestore\Affiliateplus\Controller\Adminhtml\Transaction;

use Magento\Framework\Controller\ResultFactory;

/**
 * Action Index
 */
class MassStatus extends \Magestore\Affiliateplus\Controller\Adminhtml\Affiliateplus
{
    /**
     * Execute action
     */
    public function execute()
    {
        $transactionIds = $this->getRequest()->getParam('transaction');
        if (!is_array($transactionIds) || empty($transactionIds)) {
            $this->messageManager->addError(__('Please select record(s).'));
        } else {
            /** @var \Magestore\Affiliateplus\Model\ResourceModel\Banner\Collection $collection */
            $collection = $this->_objectManager->create('Magestore\Affiliateplus\Model\ResourceModel\Transaction\Collection');
            $collection->addFieldToFilter('transaction_id', ['in' => $transactionIds])
                        ->addFieldToFilter('status', \Magestore\Affiliateplus\Model\Transaction::TRANSACTION_ONHOLD);
            try {
                $successed = 0;
                foreach ($collection as $item) {
                    $item->unHold();
                    $successed++;
                }
                if($successed) {
                    $this->messageManager->addSuccess(
                        __('Total %1 of %2 transaction(s) were unholded successfully.', $successed, count($transactionIds))
                    );
                } else {
                    __('There was no transaction unholded.');
                }

            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
