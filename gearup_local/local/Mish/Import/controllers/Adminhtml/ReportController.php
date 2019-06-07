<?php

/**
 * ImportController
 *
 * @package GearUp.me
 */
class Mish_Import_Adminhtml_ReportController extends Mage_Adminhtml_Controller_Action 
{
    /**
     * Action Feed
     */
    public function feedAction()
    {
		$this->loadLayout()
			->_setActiveMenu('report/import')
			->_addBreadcrumb(Mage::helper('import')->__('Catalog Feed'), Mage::helper('import')->__('Catalog feeds'));

        $this->_addContent($this->getLayout()->createBlock('import/adminhtml_feed_container'));
        $this->renderLayout();
    }

    /**
     * Action Update
     */
    public function updateAction()
    {
		$this->loadLayout()
			->_setActiveMenu('report/import')
			->_addBreadcrumb(Mage::helper('import')->__('Catalog Feeds'), Mage::helper('import')->__('Catalog feeds'));
        
        $this->_addContent($this->getLayout()->createBlock('import/adminhtml_update_container'));
        $this->renderLayout();
    }
}