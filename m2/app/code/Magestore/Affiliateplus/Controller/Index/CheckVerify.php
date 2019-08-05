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
class CheckVerify extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * Execute action
     */
    public function execute()
    {
        if (!$this->_dataHelper->isAffiliateModuleEnabled()) {
            return $this->_redirect($this->getBaseUrl());
        }

        $request = $this->getRequest();
        $method = $request->getParam('payment_method');
        $email = $request->getParam('email');
        $require = $this->getPaymentHelper()->isRequireAuthentication($method);
        if ($require) {
            $account = $this->getSession()->getAccount();

            $verify = $this->getModelPaymentVerify()->loadExist($account->getId(), $email, $method);
            if ($verify->isVerified()) {
                $this->getResponse()->setBody('1');
            } else {
                $this->getResponse()->setBody('');
            }
        } else {
            $this->getResponse()->setBody('1');
        }

    }


}
