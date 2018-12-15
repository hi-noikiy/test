<?php

/**
 * @author    Amasty
 * @copyright Amasty
 * @package   Amasty_Customerattr
 */
class Amasty_Fpccrawler_Block_Adminhtml_Log_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());

        if ($value) {
            $value = $value . ' (' . Mage::helper('amfpccrawler')->getStatusCodeDescription($value) . ')';
        } else {
            return '';
        }

        return $value;
    }
}