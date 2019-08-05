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
namespace Magestore\Affiliateplus\Controller\Index;

/**
 * Action Index
 */
class CancelPayment extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * Execute action
     */
    public function execute()
    {
        if (!$this->_dataHelper->isAffiliateModuleEnabled()) {
            return $this->_redirect($this->getBaseUrl());
        }

        if ($this->_accountHelper->accountNotLogin()) {
            return $this->_redirect('affiliateplus/account/login');
        }
        if ($this->_accountHelper->isNotAvailableAccount()){
            return $this->_redirect('affiliateplus/index/index');
        }

        $id = $this->getRequest()->getParam('id');
        $payment = $this->getModelPayment()->load($id);
        $account = $this->_accountHelper->getAccount();

        $limitDays = intval($this->getConfigHelper()->getPaymentConfig('cancel_days'));
        $canCancel = $limitDays ? (time() - strtotime($payment->getRequestTime()) <= $limitDays * 86400) : true;
        if (($payment->getStatus() <= 2) && ($account->getId() == $payment->getAccountId()) && $canCancel) {
            try {
                $payment->setStatus(4)->save();
                $this->messageManager->addSuccess(__('Your request has been cancelled successfully!'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('affiliateplus/index/payments');
        return $resultRedirect;
    }


}
