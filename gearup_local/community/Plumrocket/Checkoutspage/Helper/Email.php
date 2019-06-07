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


class Plumrocket_Checkoutspage_Helper_Email extends Mage_Core_Helper_Abstract
{

	/**
	 * Checkout Success Page order items template
	 * @var string
	 */
	protected $_chekoutspageOrderItemsTemplate = 'checkoutspage/email/order/items.phtml';

	/**
	 * Checkout success page order wrapper template file
	 * @var string
	 */
	protected $_chekoutspageOrderTotalsWrapperTemplate = 'checkoutspage/email/order/totals/wrapper.phtml';

		/**
	 * Checkout success page order template file
	 * @var string
	 */
	protected $_chekoutspageOrderTotalsTemplate = 'checkoutspage/email/order/totals.phtml';

	/**
	 * Checkout success page order template file
	 * @var string
	 */
	protected $_chekoutspageOrderTotalsTaxTemplate = 'checkoutspage/email/order/totals/tax.phtml';

	/**
	 * Default template for order items
	 * @var string
	 */
	protected $_defaultOrderItemsTemplate = 'email/order/items.phtml';

	/**
	 * Default order totals wrapper template
	 * @var string
	 */
	protected $_defaultOrderTotalsWrapperTemplate = 'email/order/totals/wrapper.phtml';

	/**
	 * Default order totals template
	 * @var string
	 */
	protected $_defaultOrderTotalsTemplate = 'sales/order/totals.phtml';

	/**
	 * Default order totals template
	 * @var string
	 */
	protected $_defaultOrderTotalsTaxTemplate = 'tax/order/tax.phtml';

	/**
	 * Get order items template files
	 * @return string
	 */
	public function getOrderItemsTemplate()
	{
		if ($this->_useBetterOrderEmail()) {
			return $this->_chekoutspageOrderItemsTemplate;
		}

		return $this->_defaultOrderItemsTemplate;
	}

	/**
	 * Retrieve order totals wrapper template
	 * @return string
	 */
	public function getOrderTotalsWrapperTemplate()
	{
		if ($this->_useBetterOrderEmail()) {
			return $this->_chekoutspageOrderTotalsWrapperTemplate;
		}

		return $this->_defaultOrderTotalsWrapperTemplate;
	}

	/**
	 * Retrieve order totals template
	 * @return string
	 */
	public function getOrderTotalsTemplate()
	{
		if ($this->_useBetterOrderEmail()) {
			return $this->_chekoutspageOrderTotalsTemplate;
		}

		return $this->_defaultOrderTotalsTemplate;
	}

	/**
	 * Retrieve order totals template
	 * @return string
	 */
	public function getOrderTotalsTaxTemplate()
	{
		if ($this->_useBetterOrderEmail()) {
			return $this->_chekoutspageOrderTotalsTaxTemplate;
		}

		return $this->_defaultOrderTotalsTaxTemplate;
	}

	/**
	 * Can be used templates from checkou success page or must be used default template
	 * @return boolean
	 */
	protected function _useBetterOrderEmail()
	{
		return Mage::helper('checkoutspage')->useBetterOrderEmail();
	}

}