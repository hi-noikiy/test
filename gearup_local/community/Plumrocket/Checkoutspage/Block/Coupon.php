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


class Plumrocket_Checkoutspage_Block_Coupon extends Plumrocket_Checkoutspage_Block_Abstract
{

	protected $_coupon;
	protected $_order;


	const COUPON_DEFAULT_BACKGROUND = 'default/image.jpg';


	public function __construct()
	{
		if (Mage::getSingleton('plumbase/observer')->customer() == Mage::getSingleton('plumbase/product')->currentCustomer()) {
			parent::__construct();
			$this->_order = $this->getOrder();
			return $this;
		}
	}


	public function canDisplay()
	{
		return $this->helper('checkoutspage')->canDisplayNextOrderPromoCode($this->_order)
			&& $this->getCoupon();
	}


	public function getCoupon()
	{
		if (is_null($this->_coupon)) {
			$this->_coupon = $this->helper('checkoutspage')->getNextOrderPromoCode($this->_order);
		}
		return $this->_coupon;
	}


	protected function _getSettings($field)
	{
		return (!$field) ? false : Mage::getStoreConfig('checkoutspage/coupon/'.$field);
	}


	public function getCouponMessage()
	{
		$ruleDescription = $this->helper('checkoutspage')->getRuleDescrioptinByOerder($this->_order);
		if ($ruleDescription && $ruleDescription != '') {
			return Mage::helper('cms')->getPageTemplateProcessor()->filter( $ruleDescription );
		} else {
			return Mage::helper('cms')->getPageTemplateProcessor()->filter( $this->_getSettings('message') );
		}
	}


	public function getCouponBackground()
	{
		$confBackground = $this->_getSettings('background');
		if (!empty($confBackground)) {
			$background = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'checkoutspage/' . $this->_getSettings('background');
		} else {
			$background = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'checkoutspage/' . self::COUPON_DEFAULT_BACKGROUND;
		}
		return $background;
	}

}