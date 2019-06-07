<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rewards
 * @version   1.1.35
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rewards_Block_Checkout_Cart_Usepoints extends Mage_Checkout_Block_Cart_Abstract
{
    /**
     * @return Mirasvit_Rewards_Model_Purchase
     */
    protected function getPurchase()
    {
        return Mage::helper('rewards/purchase')->getByQuote($this->getQuote());
    }

    /**
     * @deprecated method renamed
     * @return int
     */
    public function getPointsAmount()
    {
        if (!$this->getPurchase()) {
            return 0;
        }

        return $this->getPurchase()->getSpendPoints();
    }

    /**
     * @return int
     */
    public function getBalancePoints()
    {
        return Mage::helper('rewards/balance')->getBalancePoints($this->getCustomer());
    }

    /**
     * @return int
     */
    public function getMaxPointsNumberToSpent()
    {
        if (!$this->getPurchase()) {
            return 0;
        }

        return $this->getPurchase()->getMaxPointsNumberToSpent();
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if (!Mage::getModel('customer/session')->isLoggedIn()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     *  Checks, whether complex discount (e. q. coupons and rewards) are disabled
     *
     * @return boolean
     */
    public function getIsComplexDiscountDisabled()
    {
        $config = Mage::getSingleton('rewards/config');

        return $config->getGeneralIsAllowRewardsAndCoupons() == Mirasvit_Rewards_Model_Config::COUPONS_DISABLED_WARNED
            || $config->getGeneralIsAllowRewardsAndCoupons() == Mirasvit_Rewards_Model_Config::COUPONS_DISABLED_HIDDEN;
    }

    /**
     *  Checks, whether coupon block should be blocked
     *
     * @return boolean
     */
    public function getIsCouponBlocked()
    {
        $config = Mage::getSingleton('rewards/config');
        $items = array_keys($this->getQuote()->getTotals());

        return $config->getGeneralIsAllowRewardsAndCoupons() == Mirasvit_Rewards_Model_Config::COUPONS_DISABLED_WARNED
            && in_array('rewards_spend', $items);
    }

    /**
     *  Checks, whether coupon block should be hidden
     *
     * @return boolean
     */
    public function getIsCouponHidden()
    {
        $config = Mage::getSingleton('rewards/config');
        $items = array_keys($this->getQuote()->getTotals());

        return $config->getGeneralIsAllowRewardsAndCoupons() == Mirasvit_Rewards_Model_Config::COUPONS_DISABLED_HIDDEN
            && in_array('rewards_spend', $items);
    }

    /**
     *  Checks, whether spend points block should be blocked
     *
     * @return boolean
     */
    public function getIsSpendBlocked()
    {
        $config = Mage::getSingleton('rewards/config');
        $items = array_keys($this->getQuote()->getTotals());

        return $config->getGeneralIsAllowRewardsAndCoupons() == Mirasvit_Rewards_Model_Config::COUPONS_DISABLED_WARNED
            && in_array('discount', $items) && !in_array('rewards_spend', $items);
    }

    /**
     *  Checks, whether spend points block should be shown (if option COUPONS_DISABLED_HIDDEN is selected)
     *
     * @return boolean
     */
    public function getIsSpendAllowed()
    {
        $config = Mage::getSingleton('rewards/config');
        $items = array_keys($this->getQuote()->getTotals());
        if ($config->getGeneralIsAllowRewardsAndCoupons() == Mirasvit_Rewards_Model_Config::COUPONS_DISABLED_HIDDEN &&
                in_array('discount', $items) && !in_array('rewards_spend', $items)) {
            return false;
        }

        return true;
    }

    /**
     * @return boolean
     */
    public function getIsIntensoTheme()
    {
        return Mage::getSingleton('core/design_package')->getPackageName() == 'intenso';
    }

    /**
     * @return string
     */
    public function getUsePointsUrl()
    {
        $secure = Mage::app()->getFrontController()->getRequest()->isSecure();
        return $this->getUrl('rewards/checkout/applyPointsPost', array('_secure' => $secure));
    }
}
