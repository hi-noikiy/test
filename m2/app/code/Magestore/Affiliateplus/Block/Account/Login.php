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
namespace Magestore\Affiliateplus\Block\Account;

class Login extends \Magestore\Affiliateplus\Block\AbstractTemplate
{
    /**
     * @return \Magento\Framework\Session\SessionManagerInterface
     */
    protected function _getCoreSession(){
        return $this->_session;
    }

    /**
     * @return $this
     */
    public function _prepareLayout(){
        return parent::_prepareLayout();
    }

    /**
     * @return null
     */
    public function getUsername(){
        if ($loginData = $this->getLoginFormData())
            return $loginData['email'];
        return null;
    }

    /**
     * @return mixed
     */
    public function getLoginFormData(){
        return $this->_getCoreSession()->getLoginFormData();
    }

    /**
     * @return string
     */
    public function getRegisterUrl(){
        return $this->getUrl('affiliateplus/account/register');
    }

    /**
     * @return mixed
     */
    public function getRegisterDescription(){
        return $this->_configHelper->getSharingConfig('register_description');
    }

    /**
     * @param string $html
     * @return string
     */
    protected function _afterToHtml($html){
        $this->_getCoreSession()->unsetData('login_form_data');
        return parent::_afterToHtml($html);
    }
    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}