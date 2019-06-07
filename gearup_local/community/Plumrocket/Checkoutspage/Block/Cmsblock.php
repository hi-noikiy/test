<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Checkoutspage
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

class Plumrocket_Checkoutspage_Block_Cmsblock extends Mage_Core_Block_Template
{

	public function getContent()
	{
		return Mage::helper('cms')->getPageTemplateProcessor()->filter( $this->_getSettings('content') );
	}


	public function isEnabled()
	{
		return $this->_getSettings('enabled');
	}


	protected function _getSettings($field)
	{
		return Mage::getStoreConfig('checkoutspage/cms_block/'.$field);
	}

}