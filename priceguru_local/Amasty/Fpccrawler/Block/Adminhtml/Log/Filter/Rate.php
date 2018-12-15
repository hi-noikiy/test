<?php

/**
 * @author    Amasty
 * @copyright Amasty
 * @package   Amasty_Customerattr
 */
class Amasty_Fpccrawler_Block_Adminhtml_Log_Filter_Rate extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Range
{
    public function getCondition()
    {
        $value = $this->getValue();
        if (is_null($value) || $value == 0) {
            return null;
        }

        return array('or' => array('eq' => $value));
    }
}