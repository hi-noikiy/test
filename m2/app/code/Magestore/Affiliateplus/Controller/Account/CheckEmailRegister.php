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
 * Class Imagecaptcha
 * @package Magestore\Affiliateplus\Controller\Account
 */
class CheckEmailRegister extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * Execute action
     */
    public function execute()
    {
        $emailAddress = $this->getRequest()->getParam('email_address');
        $isValidEmail = true;
        if (!\Zend_Validate::is(trim($emailAddress), 'EmailAddress')) {
            $isValidEmail = false;
        }
        if ($isValidEmail) {
            $error = false;
            $websiteId = $this->_storeManager->getWebsite()->getId();

            /* edit by blanka */
            $email = $this->_getCustomerModel()->setWebsiteId($websiteId)
                ->loadByEmail($emailAddress);
            /* end edit */
            if ($email->getId()) {
                $error = true;
            }
            if ($error != '') {
                $html = "<div class='mage-error'>" . __('The email %1 belongs to a customer. If it is your email address, you can use it to <a href="%2">login</a> our system.', $emailAddress, $this->getBaseUrl().'affiliateplus/account/login') . "</div>";
                $html .= '<input type="hidden" id="is_valid_email" value="0"/>';
            } else {
                $html = "<div class='mage-success'>" . __('You can use this email address.') . "</div>";
                $html .= '<input type="hidden" id="is_valid_email" value="1"/>';
            }
        } else {
            $html = "<div class='mage-error'>" . __('Invalid email address.') . "</div>";
            $html .= '<input type="hidden" id="is_valid_email" value="1"/>';
        }
        $this->getResponse()->setBody($html);
    }

    protected function _getCustomerModel(){
        return $this->_objectManager->create('Magento\Customer\Model\Customer');
    }
}
