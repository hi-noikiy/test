<?php

/**
 * @author    Amasty
 * @copyright Amasty
 * @package   Amasty_Customerattr
 */
class Amasty_Fpccrawler_Block_Adminhtml_Log_Filter_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
    public function getCondition()
    {
        $value = (int)$this->getValue();
        if (is_null($value) || $value == 0) {
            return null;
        }

        return array('or' => array('eq' => $value));
    }
}