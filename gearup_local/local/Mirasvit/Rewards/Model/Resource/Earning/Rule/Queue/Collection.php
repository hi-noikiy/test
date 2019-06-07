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
 * @method Mirasvit_Rewards_Model_Earning_Queue getFirstItem()
 * @method Mirasvit_Rewards_Model_Earning_Queue getLastItem()
 * @method Mirasvit_Rewards_Model_Resource_Earning_Rule_Queue_Collection|Mirasvit_Rewards_Model_Earning_Queue[] addFieldToFilter
 * @method Mirasvit_Rewards_Model_Resource_Earning_Rule_Queue_Collection|Mirasvit_Rewards_Model_Earning_Queue[] setOrder
 */
class Mirasvit_Rewards_Model_Resource_Earning_Rule_Queue_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('rewards/earning_rule_queue');
    }

    protected function initFields()
    {
        $select = $this->getSelect();
        // $select->columns(array('is_replied' => new Zend_Db_Expr("answer <> ''")));
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->initFields();
    }

     /************************/
}
