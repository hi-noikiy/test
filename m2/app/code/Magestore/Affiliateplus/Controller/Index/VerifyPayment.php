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
class VerifyPayment extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * Execute action
     */
    public function execute()
    {
        if (!$this->_dataHelper->isAffiliateModuleEnabled()) {
            return $this->_redirect($this->getBaseUrl());
        }
        $block = $this->_blockFactory->createBlock('Magestore\Affiliateplus\Block\Payment\Verify');
        $block->setTemplate('Magestore_Affiliateplus::payment/verify.phtml');
        $method = $this->getRequest()->getParam('method');
        $email = $this->getRequest()->getParam('email');

        $account = $this->getSession()->getAccount();
        if ($email) {
            try {
                $account = $this->getModelAccount()->load($account->getId());
                $account->setData($method . '_email', $email)
                    ->save();
            } catch (\Exception $e) {

            }
        }
        $verify = $this->getModelPaymentVerify()->loadExist($account->getId(), $email, $method);
        if (!$verify->getId()) {
            $verify->setData('payment_method', $method);
            $verify->setData('account_id', $account->getId());
            $verify->setData('field', $email);

            $code = $verify->sendMailAuthentication($email, $method);
            if ($code) {
                $verify->setData('info', $code);
                try {
                    $verify->save();
                } catch (\Exception $e) {

                }
            } else {
                $block->setError('1');
            }
        }
        $this->getResponse()->setBody($block->toHtml());

    }


}
