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
namespace Magestore\Affiliateplus\Controller\Checkout;

class CreditPost extends \Magestore\Affiliateplus\Controller\AbstractAction
{

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if(!$this->_dataHelper->isAffiliateModuleEnabled()){
            return $this->_redirect($this->getBaseUrl());
        }

        if ($this->getRequest()->isPost()) {
            $session = $this->_objectManager->create('Magento\Checkout\Model\Session');
            if ($this->getRequest()->getPost('affiliateplus_credit')) {
                $creditAmount = floatval($this->getRequest()->getPost('credit_amount'));
                if(!$this->getAffiliateBalance()){
                    $this->messageManager->addSuccess(__('Your affiliate store credit is not enough to use'));
                } else{
                    if($this->getAffiliateBalance() >= $creditAmount){
                        $session->setUseAffiliateCredit(true);
                        $session->setAffiliateCredit($creditAmount);
                    }else{
                        $session->setUseAffiliateCredit(true);
                        $session->setAffiliateCredit($this->getAffiliateBalance());
                    }
                    $this->messageManager->addSuccess(__('Your affiliate store credit has been applied successfully'));
                }
            } else {
                $session->setUseAffiliateCredit(false);
            }
        }
        return $this->_redirect('checkout/cart');
    }

    /**
     * @return int
     */
    protected function getAffiliateBalance(){
        $account = $this->_affiliateSession->getAccount();
        if($account && $account->getId()){
            return $this->getConfigHelper()->convertPrice($account->getBalance());
        }
        return 0;
    }
}