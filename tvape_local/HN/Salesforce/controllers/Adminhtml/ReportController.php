<?php
class HN_Salesforce_Adminhtml_ReportController extends Mage_Adminhtml_Controller_Action {
	
	public function indexAction() {
		$this->_title($this->__('SalesforceCRM Integration Mapping Fields'));
	
		$this->loadLayout ()->_setActiveMenu ( 'salesforce/fieldmapping' );
	
		$this->_addContent($this->getLayout()->createBlock('salesforce/adminhtml_report'));
	
		$this->renderLayout ();
	}

	public function massDeleteAction()
	{
		$id = $this->getRequest()->getParam('id');

		if(!is_array($id)) {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please select records.'));
		} else {
			try {
				$model = Mage::getSingleton('salesforce/report');
				foreach ($id as $adId) {
					$model->load($adId)->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Total of %d record(s) were deleted.', count($id)));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/');
	}

	public function exportCsvAction() {
		$fileName = 'sync_report_salesforce.csv';
		$content = $this->getLayout()
		->createBlock('salesforce/adminhtml_report_grid')
		->getCsv();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	
	/**
	 * export grid staff to XML type
	 */
	public function exportXmlAction() {
		$fileName = 'sync_report_salesforce.xml';
		$content = $this->getLayout()
		->createBlock('salesforce/adminhtml_report_grid')
		->getXml();
		$this->_prepareDownloadResponse($fileName, $content);
	}

	protected function _isAllowed()
	{
	    return Mage::getSingleton('admin/session')->isAllowed('salesforce/report');  
	}
}
