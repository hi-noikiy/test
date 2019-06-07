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



class Mirasvit_Rewards_Block_Adminhtml_Sales_Order_Spend extends Mage_Core_Block_Template
{

    /**
     * @var int $balance
     */
    protected $balance;

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _getOrderCreateModel()
    {
        return Mage::getSingleton('adminhtml/sales_order_create');
    }

    /**
     * @return int
     */
    public function getBalance()
    {
        if (!$this->balance) {
            $quote = $this->_getOrderCreateModel()->getQuote();

            if (!$quote || !$quote->getCustomerId()) {
                return false;
            }

            $this->balance = Mage::helper('rewards/balance')->getBalancePoints($quote->getCustomerId());
        }

        return $this->balance;
    }

    /**
     * @return int
     */
    public function getMaxUsePoints()
    {
        $quote = $this->_getOrderCreateModel()->getQuote();
        $purchase = Mage::helper('rewards/purchase')->getByQuote($quote);
        if (!$purchase) {
            return 0;
        }

        return $purchase->getSpendMaxPoints();
    }

    /**
     * @return int
     */
    public function getSpentPoints()
    {
        $quote = $this->_getOrderCreateModel()->getQuote();
        $purchase = Mage::helper('rewards/purchase')->getByQuote($quote);
        if (!$purchase) {
            return 0;
        }

        return $purchase->getSpendPoints();

    }

}
