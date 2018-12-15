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
 * @package   mirasvit/extension_helpdesk
 * @version   1.5.4
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Model_Config_Source_Followupperiod
{
    public function toArray()
    {
        return array(
            Mirasvit_Helpdesk_Model_Config::FOLLOWUPPERIOD_MINUTES => Mage::helper('helpdesk')->__('In minutes...'),
            Mirasvit_Helpdesk_Model_Config::FOLLOWUPPERIOD_HOURS => Mage::helper('helpdesk')->__('In hours...'),
            Mirasvit_Helpdesk_Model_Config::FOLLOWUPPERIOD_DAYS => Mage::helper('helpdesk')->__('In days...'),
            Mirasvit_Helpdesk_Model_Config::FOLLOWUPPERIOD_WEEKS => Mage::helper('helpdesk')->__('In weeks...'),
            Mirasvit_Helpdesk_Model_Config::FOLLOWUPPERIOD_MONTHS => Mage::helper('helpdesk')->__('In months...'),
            Mirasvit_Helpdesk_Model_Config::FOLLOWUPPERIOD_CUSTOM => Mage::helper('helpdesk')->__('Custom'),
        );
    }
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
