<?php
/**
 * Magestore
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
 * @package     Magestore_Sociallogin
 * @module      Sociallogin
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

class Magestore_Sociallogin_Block_Ljlogin extends Mage_Core_Block_Template
{
    /**
     * @return mixed
     */
    public function getLoginUrl(){
		return $this->getUrl('sociallogin/ljlogin/login');
		//return Mage::getModel('sociallogin/mylogin')->getMyLoginUrl();
	}

    /**
     * @return mixed
     */
    public function getSetBlock(){
        return $this->getUrl('sociallogin/ljlogin/setBlock');        
    }

    /**
     * @return mixed
     */
    public function setBackUrl(){
		$currentUrl = Mage::helper('core/url')->getCurrentUrl();
		Mage::getSingleton('core/session')->setBackUrl($currentUrl);
		return $currentUrl;
	}

    /**
     * @return mixed
     */
    protected function _beforeToHtml()
	{
		return parent::_beforeToHtml();
	}		
}