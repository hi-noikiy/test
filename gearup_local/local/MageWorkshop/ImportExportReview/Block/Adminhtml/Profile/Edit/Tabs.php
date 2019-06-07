<?php

class MageWorkshop_ImportExportReview_Block_Adminhtml_Profile_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('mageworkshop_importexportreview_profile_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('mageworkshop_importexportreview')->__('Reviews Import/Export Profile'));
    }

    protected function _beforeToHtml()
    {
        $new = !Mage::registry('current_drie_profile')->getId();

        $this->addTab('edit', array(
            'label'     => Mage::helper('mageworkshop_importexportreview')->__('Profile Configuration'),
            'content'   => $this->getLayout()->createBlock('mageworkshop_importexportreview_adminhtml/profile_edit_tab_edit')->toHtml()
        ));

        if (!$new) {
            $this->addTab('run', array(
                'label'     => Mage::helper('mageworkshop_importexportreview')->__('Run Profile'),
                'content'   => $this->getLayout()->createBlock('mageworkshop_importexportreview_adminhtml/profile_edit_tab_run')
                    ->setData('run_import', $this->getData('run_import'))
                    ->setData('yotpo', $this->getData('yotpo'))
                    ->toHtml()
            ));
        }
        $this->setActiveTab($this->getNeededTabId());

        return parent::_beforeToHtml();
    }
}
