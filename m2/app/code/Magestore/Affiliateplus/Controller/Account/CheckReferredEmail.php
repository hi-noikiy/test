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
namespace Magestore\Affiliateplus\Controller\Account;

/**
 * Class CheckReferredEmail
 * @package Magestore\Affiliateplus\Controller\Account
 */
class CheckReferredEmail extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * Execute action
     */
    public function execute(){
        $emailAddress = $this->getRequest()->getParam('email_address');
        $isvalidEmail = true;
        if (!\Zend_Validate::is(trim($emailAddress), 'EmailAddress')) {
            $isvalidEmail = false;
        }
        if ($isvalidEmail) {
            $error = true;

            $affiliate = $this->getAffiliateAccountModel()->load($emailAddress, 'email');
            if ($affiliate && $affiliate->getId()) {
                $error = false;
            }
            if (!$error) {
                $html = "<div class='mage-success'>".__('You are referring by %1', $affiliate->getName())."</div>";
                $html .= '<input type="hidden" id="is_valid_referredemail" value="1"/>';
            } else {
                $html = "<div class='mage-error'>" . __('There is no affiliate with email address %1. Please enter a different one.', $emailAddress) . "</div>";
                $html .= '<input type="hidden" id="is_valid_referredemail" value="1"/>';
            }
        } else {
            $html = "<div class='mage-error'>" . __('Invalid email address.') . "</div>";
            $html .= '<input type="hidden" id="is_valid_referredemail" value="1"/>';
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * get affiliate account model
     */
    protected function getAffiliateAccountModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Account');
    }
}
