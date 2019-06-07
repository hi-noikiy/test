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



/**
 * @method Mirasvit_Rewards_Model_Resource_Notification_Rule_Collection |
 *         Mirasvit_Rewards_Model_Notification_Rule[] getCollection()
 * @method Mirasvit_Rewards_Model_Notification_Rule load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Rewards_Model_Notification_Rule setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Rewards_Model_Notification_Rule setIsMassStatus(bool $flag)
 * @method Mirasvit_Rewards_Model_Resource_Notification_Rule getResource()
 */
class Mirasvit_Rewards_Model_Notification_Rule extends Mage_Rule_Model_Rule
{
    const TYPE_PRODUCT = 'product';
    const TYPE_CART = 'cart';
    const TYPE_CUSTOM = 'custom';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rewards/notification_rule');
    }

    /**
     * @param bool $emptyOption
     * @return array;
     */
    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /**
     * @return Mirasvit_Rewards_Model_Earning_Rule_Condition_Combine;
     */
    public function getConditionsInstance()
    {
        return Mage::getModel('rewards/notification_rule_condition_combine');
    }

    /**
     * @return Mage_SalesRule_Model_Rule_Condition_Product_Combine
     */
    public function getActionsInstance()
    {
        return Mage::getModel('salesrule/rule_condition_product_combine');
    }

    /**
     * @return array
     */
    public function getProductIds()
    {
        return $this->_getResource()->getRuleProductIds($this->getId());
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        $message = $this->getData('message');
        if ($purchase = Mage::helper('rewards/purchase')->getPurchase()) {
            $earnPoints = Mage::helper('rewards')->formatPoints($purchase->getEarnPoints());
            $spentPoints = Mage::helper('rewards')->formatPoints($purchase->getSpendPoints());

            $message = str_replace('[earn_points]', $earnPoints, $message);
            $message = str_replace('[spend_points]', $spentPoints, $message);
        }

        return $message;
    }

    /**
     * @param string $format
     * @return string
     */
    public function toString($format = '')
    {
        $this->load($this->getId());
        $string = $this->getConditions()->asStringRecursive();

        $string = nl2br(preg_replace('/ /', '&nbsp;', $string));

        return $string;
    }
    /************************/

    /**
     * @return void
     */
    public function applyAll()
    {
        $this->_getResource()->applyAllRulesForDateRange();
        // $this->_invalidateCache();
    }

    /**
     * @return array
     */
    public function getWebsiteIds()
    {
        return $this->getData('website_ids');
    }

    /**
     * @return void
     */
    public function _beforeSave()
    {
        if (is_array($this->getData('type'))) {
            $this->setData('type', implode(',', $this->getData('type')));
        }
        parent::_beforeSave();
    }

    /**
     * @return array
     */
    public function getType()
    {
        $type = parent::getType();
        if (is_string($type)) {
            return explode(',', $type);
        }

        return $type;
    }
}
