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


class Plumrocket_Checkoutspage_Block_Rewards extends Plumrocket_Checkoutspage_Block_Abstract
{

	protected $_earnedPoints;
	protected $_redeemedPoints;
	protected $_points;
	protected $_isPotential = false;

	public function rewardsEnabled()
	{
		return Mage::helper('checkoutspage')->checkRewardPoints() && (bool)Mage::getStoreConfig('checkoutspage/reward_point/enabled') && $this->getOrder()->getCustomerId();
	}


	public function getEarnedPoints()
	{
		if (is_null($this->_earnedPoints)) {
			if ($order = $this->getOrder()) {
				$history = Mage::getModel('rewards/history')->getItem($order->getId(), Plumrocket_Rewards_Model_History::OBJ_TYPE_ORDER_CREDITS, 1);

				if (!$this->_earnedPoints = $history->getPoints()) {
					$this->_earnedPoints = Mage::helper('rewards')->getOrderPotentialPoints($order->getIncrementId());
					$this->_isPotential = true;
				}

			} else {
				$this->_earnedPoints = 0;
			}
		}
		return $this->_earnedPoints;
	}


	public function isPotential()
	{
		return $this->_isPotential;
	}


	public function getRedeemedPoints()
	{
		if (is_null($this->_redeemedPoints)) {
			if ($order = $this->getOrder()) {
				$history = Mage::getModel('rewards/history')->getItem($order->getId(), Plumrocket_Rewards_Model_History::OBJ_TYPE_ORDER);
				$this->_redeemedPoints = $history->getPoints();
			} else {
				$this->_redeemedPoints = 0;
			}
		}
		return $this->_redeemedPoints;
	}


	public function getPoints()
	{
		if (is_null($this->_points)) {
			$customerId = $this->getOrder()->getCustomerId();
			$this->_points = Mage::getModel('rewards/points')->getByCustomer($customerId);
		}
		return $this->_points;
	}


	public function getAvailablePoints()
	{
		return $this->getPoints()->getAvailable();
	}


	public function getCurrentMoney()
	{
		$points = $this->getPoints();
		$amount = $points->currencyExchange(
			$points->getAvailable() / $points->getConfig()->getRedeemPointsRate(),
			true
		);

		return Mage::helper('core')->currency($amount, true, false);
	}


	public function escapePoints($points)
	{
		if (!$points)  {
			return 0;
		}
		return $points;
	}
}