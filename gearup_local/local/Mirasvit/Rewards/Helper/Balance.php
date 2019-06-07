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



class Mirasvit_Rewards_Helper_Balance extends Mage_Core_Helper_Abstract
{
    /**
     * Returns points, awarded for a product purchase
     *
     * @param Mage_Catalog_Model_Product $product
     * @return int
     *
     * @deprecated we need it for compatibility with older versions. DON'T REMOVE.
     */
    public function getProductPoints($product)
    {
        return Mage::helper('rewards/balance_earn')->getProductPoints($product);
    }

    /**
     * Change the number of points on the customer balance.
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param int $pointsNum
     * @param string $historyMessage
     * @param bool $code          - optional, if we have code, we will check for uniqness this this transaction
     * @param bool $notifyByEmail
     * @param bool $emailMessage
     *
     * @return bool
     */
    public function changePointsBalance($customer, $pointsNum, $historyMessage, $code = false, $notifyByEmail = false,
        $emailMessage = false
    ) {
        if (is_object($customer)) {
            $customer = $customer->getId();
        }
        if ($code) {
            $collection = Mage::getModel('rewards/transaction')->getCollection()
                            ->addFieldToFilter('customer_id', $customer)
                            ->addFieldToFilter('code', $code);
            if ($code == Mirasvit_Rewards_Model_Config::BEHAVIOR_TRIGGER_LOGIN) {
                $currentDate = Mage::getSingleton('core/date')->gmtDate();
                $collection->getSelect()
                    ->where("DATEDIFF('{$currentDate}', main_table.created_at) < 1");
            }
            if ($collection->count()) {
                return false;
            }
        }
        $transaction = Mage::getModel('rewards/transaction')
            ->setCustomerId($customer)
            ->setAmount($pointsNum);
        if ($code) {
            $transaction->setCode($code);
        }
        $currentStore = Mage::helper('rewards')->getCurrentStore();
        Mage::helper('rewards')->setCurrentStore(Mage::getModel('customer/customer')->load($customer)->getStore());
        $historyMessage = Mage::helper('rewards/mail')->parseVariables(Mage::helper('rewards')->____($historyMessage),
            $transaction);
        $transaction->setComment($historyMessage);
        $transaction->save();
        if ($notifyByEmail) {
            Mage::helper('rewards/mail')->sendNotificationBalanceUpdateEmail($transaction, $emailMessage);
        }
        Mage::helper('rewards')->setCurrentStore($currentStore);

        return $transaction;
    }

    /**
     * Returns current customer's balance points.
     * 
     * @param Mage_Customer_Model_Customer $customer
     * @return int
     */
    public function getBalancePoints($customer)
    {
        if (is_object($customer)) {
            $customer = $customer->getId();
        }
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('rewards/transaction');
        $sum = (int) $readConnection->fetchOne("SELECT SUM(amount) FROM $table WHERE customer_id=?",
            array((int) $customer));

        return $sum;
    }
}
