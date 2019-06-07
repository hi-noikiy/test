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
 * @method Mirasvit_Rewards_Model_Resource_Earning_Rule_Collection|Mirasvit_Rewards_Model_Earning_Rule[] getCollection()
 * @method Mirasvit_Rewards_Model_Earning_Rule load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Rewards_Model_Earning_Rule setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Rewards_Model_Earning_Rule setIsMassStatus(bool $flag)
 * @method Mirasvit_Rewards_Model_Resource_Earning_Rule getResource()
 */
class Mirasvit_Rewards_Model_Earning_Rule extends Mage_Rule_Model_Rule
{
    const TYPE_PRODUCT = 'product';
    const TYPE_CART = 'cart';
    const TYPE_BEHAVIOR = 'behavior';

    protected function _construct()
    {
        $this->_init('rewards/earning_rule');
    }

    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /** Rule Methods **/
    public function getConditionsInstance()
    {
        $combine = Mage::getModel('rewards/earning_rule_condition_combine');
//        $combine->setPrefix($this->getBehaviorTrigger());
        return $combine;
    }

    public function getActionsInstance()
    {
        return Mage::getModel('salesrule/rule_condition_product_combine');
        // return Mage::getModel('rewards/earning_rule_action_collection');
    }

    public function getProductIds()
    {
        return $this->_getResource()->getRuleProductIds($this->getId());
    }

    public function toString($format = '')
    {
        $this->load($this->getId());
        $string = $this->getConditions()->asStringRecursive();

        $string = nl2br(preg_replace('/ /', '&nbsp;', $string));

        return $string;
    }
    public function getEmailMessage()
    {
        return Mage::helper('rewards/storeview')->getStoreViewValue($this, 'email_message');
    }

    public function setEmailMessage($value)
    {
        Mage::helper('rewards/storeview')->setStoreViewValue($this, 'email_message', $value);

        return $this;
    }

    public function getHistoryMessage()
    {
        return Mage::helper('rewards/storeview')->getStoreViewValue($this, 'history_message');
    }

    public function setHistoryMessage($value)
    {
        Mage::helper('rewards/storeview')->setStoreViewValue($this, 'history_message', $value);

        return $this;
    }

    public function addData(array $data)
    {
        if (isset($data['email_message']) && strpos($data['email_message'], 'a:') !== 0) {
            $this->setEmailMessage($data['email_message']);
            unset($data['email_message']);
        }

        if (isset($data['history_message']) && strpos($data['history_message'], 'a:') !== 0) {
            $this->setHistoryMessage($data['history_message']);
            unset($data['history_message']);
        }

        return parent::addData($data);
    }
    /************************/

    public function applyAll()
    {
        $this->_getResource()->applyAllRulesForDateRange();
        // $this->_invalidateCache();
    }

    public function getWebsiteIds()
    {
        return $this->getData('website_ids');
    }

    /**
     * @param array $data
     *
     * @return Mage_Rule_Model_Abstract
     */
    public function loadPost(array $data)
    {
        if (version_compare(Mage::getVersion(), '1.6.0.0', '>')) {
            return parent::loadPost($data);
        } else {
            $arr = $this->_convertFlatToRecursive($data);
            if (isset($arr['conditions'])) {
                $this->getConditions()->setConditions(array())->loadArray($arr['conditions'][1]);
            }
            if (isset($arr['actions'])) {
                if (isset($arr['actions'][1]['actions'])) {
                    $arr['actions'][1]['conditions'] = $arr['actions'][1]['actions'];
                }
                $this->getActions()->setActions(array())->loadArray($arr['actions'][1]);
            }

            return $this;
        }
    }
}
