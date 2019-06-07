<?php

class AW_All_Adminhtml_Awall_AdditionalController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this
            ->loadLayout()
            ->_title($this->__('aheadWorks - Additional Info View'))
            ->renderLayout();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/awall/awall_additional');
    }
}