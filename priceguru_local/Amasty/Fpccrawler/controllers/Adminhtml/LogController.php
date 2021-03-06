<?php

/**
 * @copyright   Copyright (c) 2009-2014 Amasty (http://www.amasty.com)
 */
class Amasty_Fpccrawler_Adminhtml_LogController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('report/amfpccrawler');
        $this->_addContent($this->getLayout()->createBlock('amfpccrawler/adminhtml_log'));
        $this->renderLayout();
    }

    protected function _setActiveMenu($menuPath)
    {
        $this->getLayout()->getBlock('menu')->setActive($menuPath);
        $this->_title($this->__('Reports'))->_title($this->__('FPC Crawler Log'));

        return $this;
    }

    protected function _title($text = null, $resetIfExists = true)
    {
        if (Mage::helper('ambase')->isVersionLessThan(1, 4)) {
            return $this;
        }

        return parent::_title($text, $resetIfExists);
    }
}
