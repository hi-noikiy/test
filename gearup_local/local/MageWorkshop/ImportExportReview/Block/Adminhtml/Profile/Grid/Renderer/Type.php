<?php

class MageWorkshop_ImportExportReview_Block_Adminhtml_Profile_Grid_Renderer_Type extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        $label = ($value == MageWorkshop_ImportExportReview_Model_Profile::EXPORT) ? 'Export' : 'Import';
        return Mage::helper('mageworkshop_importexportreview')->__($label);
    }
}