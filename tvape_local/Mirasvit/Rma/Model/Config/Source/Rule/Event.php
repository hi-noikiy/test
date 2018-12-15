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
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Model_Config_Source_Rule_Event
{
    /**
     * Returns array of Workflow Rule Events
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            Mirasvit_Rma_Model_Config::RULE_EVENT_RMA_CREATED => Mage::helper('rma')->__('New RMA has been created'),
            Mirasvit_Rma_Model_Config::RULE_EVENT_RMA_UPDATED => Mage::helper('rma')->__('RMA has been changed'),
            Mirasvit_Rma_Model_Config::RULE_EVENT_NEW_CUSTOMER_REPLY
                => Mage::helper('rma')->__('New reply from customer'),
            Mirasvit_Rma_Model_Config::RULE_EVENT_NEW_STAFF_REPLY => Mage::helper('rma')->__('New reply from staff'),
            Mirasvit_Rma_Model_Config::RULE_EVENT_CRON_HOUR_CHECK => Mage::helper('rma')->__('Check every hour'),
        );
    }
    /**
     * Returns array of Workflow Rule Events as Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = array();
        foreach ($this->toArray() as $k => $v) {
            $result[] = array('value' => $k, 'label' => $v);
        }

        return $result;
    }

    /************************/
}
