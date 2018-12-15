<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/
class Magebuzz_Outstocknotification_Adminhtml_OutstocknotificationController extends Mage_Adminhtml_Controller_action {
	protected function _initAction() {
		$this->loadLayout()
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		$this->_setActiveMenu('report/outstock');
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function gridAction() {
		$this->loadLayout();
		$this->getResponse()->setBody($this->getLayout()->createBlock('outstocknotification/adminhtml_outstocknotification_grid')->toHtml());
	}
  
  public function alertsStockGridAction() {
		$this->loadLayout(false);
		$this->renderLayout();
	}
}