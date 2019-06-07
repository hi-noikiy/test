<?php

class MageWorkshop_ImportExportReview_Block_Adminhtml_Profile_Edit_Tab_Run extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('detailedreview/drie/system/convert/profile/run.phtml');
    }

    /**
     * @return mixed
     */
    public function getRunButtonHtml()
    {
        $onclick = ($this->_getProfileType() == MageWorkshop_ImportExportReview_Model_Profile::EXPORT) ? 'runProfile(false)' : 'runProfile(true)';
        $html = $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
            ->setClass('save')->setLabel($this->__('Run Profile'))
            ->setOnClick($onclick)
            ->toHtml();

        return $html;
    }

    /**
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('*/*/uploadFile', array('id' => $this->getProfileId()));
    }

    /**
     * @return bool
     */
    public function canShowForm()
    {
        if ($this->_getCurrentProfile()->getType() == MageWorkshop_ImportExportReview_Model_Profile::IMPORT) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function showRunButton()
    {
        if ($this->canShowForm()) {
            if (!$this->getData('run_import')) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isYotpoImport()
    {
        if ($this->getData('yotpo')) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getProfileId()
    {
        return $this->_getCurrentProfile()->getId();
    }

    /**
     * @return mixed
     */
    protected function _getProfileType()
    {
        return $this->_getCurrentProfile()->getType();
    }

    /**
     * @return MageWorkshop_ImportExportReview_Model_Profile
     */
    protected function _getCurrentProfile()
    {
        return Mage::registry('current_drie_profile');
    }

    /**
     * @return array
     */
    public function getImportedFiles()
    {
        $files = array();
        $path = Mage::app()->getConfig()->getTempVarDir().'/import';
        if (!is_readable($path)) {
            return $files;
        }
        $dir = dir($path);
        while (false !== ($entry = $dir->read())) {
            if($entry != '.'
               && $entry != '..'
               && in_array(strtolower(substr($entry, strrpos($entry, '.')+1)), array($this->getParseType())))
            {
                $files[] = $entry;
            }
        }
        sort($files);
        $dir->close();
        return $files;
    }

    public function getParseType()
    {
        $data = Mage::registry('current_drie_profile')->getGuiData();
        if ($data)
            return ($data['parse']['type'] == 'excel_xml') ? 'xml': $data['parse']['type'];
    }
}
