<?php

class MageWorkshop_ImportExportReview_Block_Adminhtml_Profile extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected function _construct()
    {
        parent::_construct();

        $this->_blockGroup = 'mageworkshop_importexportreview_adminhtml';
        $this->_controller = 'profile';
        $this->_headerText = Mage::helper('mageworkshop_importexportreview')->__('Reviews Import/Export Profiles');
    }

    public function getCreateUrl()
    {
        return $this->getUrl('adminhtml/drie_profile/edit');
    }
}