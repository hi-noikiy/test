<?php

class MageWorkshop_ImportExportReview_Block_Adminhtml_History extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected function _construct()
    {
        parent::_construct();

        $this->_blockGroup = 'mageworkshop_importexportreview_adminhtml';
        $this->_controller = 'history';
        $this->_headerText = Mage::helper('mageworkshop_importexportreview')->__('Reviews Sync History');
    }
}