<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


class Amasty_Sorting_Model_System_Config_Backend_Customposition extends Mage_Core_Model_Config_Data
{
    const METHOD = 'method';
    const CUSTOM_POSITION = 'custom_position';

    protected function _afterLoad()
    {
        $value = $this->getValue();
        $value = Mage::helper('amsorting/customposition')->makeArrayFieldValue($value);
        $this->setValue($value);
    }

    protected function _beforeSave()
    {
        $value = $this->getValue();
        $value = Mage::helper('amsorting/customposition')->makeStorableArrayFieldValue($value);
        $this->setValue($value);
    }
}
