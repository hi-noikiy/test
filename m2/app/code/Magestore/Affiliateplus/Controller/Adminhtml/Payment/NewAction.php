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
namespace Magestore\Affiliateplus\Controller\Adminhtml\Payment;

use Magento\Framework\Controller\ResultFactory;

/**
 * Action NewAction
 */
class NewAction extends \Magestore\Affiliateplus\Controller\Adminhtml\Affiliateplus
{
    /**
     * Execute action
     */
    public function execute()
    {
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $paymentId = $this->getRequest()->getParam('payment_id');
        if ($accountId = $this->getRequest()->getParam('account_id')) {
            if(!$paymentId){
                $waitingPayment = $this->_objectManager->create('Magestore\Affiliateplus\Model\ResourceModel\Payment\Collection')
                    ->addFieldToFilter('account_id', $accountId)
                    ->addFieldToFilter('status', 1)
                    ->getFirstItem();
                if ($waitingPayment && $waitingPayment->getId()) {
                    $this->messageManager->addNotice(__('This account has already had a pending payment!'));
                }
                return $resultForward->forward('edit');
            } else {
                return $resultRedirect->setPath('*/*/edit', ['account_id'=>$accountId, 'payment_id'=>$paymentId]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}