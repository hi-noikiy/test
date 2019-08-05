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

/**
 * @category Magestore
 * @package  Magestore_Affiliateplus
 * @module   Affiliateplus
 * @author   Magestore Developer
 */
class ChangeUseCredit extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|void
     */
    public function execute()
    {
        if(!$this->_dataHelper->isAffiliateModuleEnabled()){
            return $this->_redirect($this->getBaseUrl());
        }

        if ($this->_accountHelper->disableStoreCredit()) {
            return ;
        }

        $session = $this->_objectManager->create('Magento\Checkout\Model\Session');
        $session->setUseAffiliateCredit($this->getRequest()->getParam('usedaffiliatepluscredit'));

        if ($this->getRequest()->getParam('usedaffiliatepluscredit') == 1) {
            $account = $this->_affiliateSession->getAccount();
            $session->setAffiliateplusBalance(floatval($account->getBalance()));
        }

        $result = array();
        $updatepayment = ($session->getQuote()->getGrandTotal() < 0.001);
        $session->getQuote()->collectTotals()->save();
        $result = $this->_objectManager->create('Magestore\Affiliateplus\Block\Credit\Form')->getAffiliateCreditInfo();

        if ($updatepayment xor ( $session->getQuote()->getGrandTotal() < 0.001)) {
            $result['updatepayment'] = 1;
        }
        return $this->getResponse()->setBody(\Zend_Json::encode($result));
    }
}
