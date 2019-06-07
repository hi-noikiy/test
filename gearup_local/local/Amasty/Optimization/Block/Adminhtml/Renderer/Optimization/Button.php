<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Block_Adminhtml_Renderer_Optimization_Button
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return mixed
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $value = $this->__(
            'Run Optimization Process (%s file(s) still in queue)',
            Mage::getResourceModel('amoptimization/task_collection')->getSize()
        );
        $html = $element
            ->setValue($value)
            ->setClass('form-button')
            ->setOnclick('location.href=&quot;' . $this->getUrl('adminhtml/amoptimization_process/run') . '&quot;')
            ->getElementHtml();

        return $html;
    }
}
